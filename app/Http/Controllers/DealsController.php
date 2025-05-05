<?php

namespace App\Http\Controllers;

use App\Models\Deal;
use App\Services\YandexDiskService;
use App\Models\User;
use App\Models\DealChangeLog;
use App\Models\DealFeed;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class DealsController extends Controller
{
    protected $yandexDiskService;

    public function __construct(YandexDiskService $yandexDiskService)
    {
        $this->yandexDiskService = $yandexDiskService;

        // Увеличиваем лимит времени выполнения и памяти для загрузки больших файлов
        ini_set('upload_max_filesize', '700M');
        ini_set('post_max_size', '700M');
        ini_set('max_execution_time', '300'); // 5 минут
        ini_set('max_input_time', '300'); // 5 минут
        ini_set('memory_limit', '1024M'); // 1 ГБ
    }

    /**
     * Отображение списка сделок.
     */
    public function dealCardinator(Request $request)
    {
        $title_site = "Сделки | Личный кабинет Экспресс-дизайн";
        $user = Auth::user();

        $search = $request->input('search');
        $status = $request->input('status');
        $view_type = $request->input('view_type', 'blocks');
        $viewType = $view_type;
        
        // Параметры фильтрации
        $package = $request->input('package');
        $priceServiceOption = $request->input('price_service_option');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $partnerId = $request->input('partner_id');
        $sortBy = $request->input('sort_by');

        $query = Deal::query();

        // Фильтр по роли пользователя
        if ($user->status === 'admin') {
            // без фильтра для админа
        } elseif ($user->status === 'partner') {
            $query->where('office_partner_id', $user->id);
        } elseif ($user->status === 'coordinator') {
            $query->where('coordinator_id', $user->id);
        } elseif (in_array($user->status, ['architect', 'designer', 'visualizer'])) {
            $query->whereHas('users', function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->where('role', $user->status);
            });
        } else {
            $query->whereHas('users', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }

        // Применяем поиск
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('client_phone', 'LIKE', "%{$search}%")
                  ->orWhere('client_email', 'LIKE', "%{$search}%")
                  ->orWhere('project_number', 'LIKE', "%{$search}%")
                  ->orWhere('package', 'LIKE', "%{$search}%")
                  ->orWhere('deal_note', 'LIKE', "%{$search}%")
                  ->orWhere('client_city', 'LIKE', "%{$search}%")
                  ->orWhere('total_sum', 'LIKE', "%{$search}%");
            });
        }

        // Фильтр по статусу
        if ($request->has('statuses') && is_array($request->statuses) && count($request->statuses) > 0) {
            $query->whereIn('status', $request->statuses);
        } elseif ($request->has('status') && !empty($request->status)) {
            $query->where('status', $request->status);
        }
        
        // Применяем дополнительные фильтры
        if ($package) $query->where('package', $package);
        if ($priceServiceOption) $query->where('price_service_option', $priceServiceOption);
        if ($dateFrom) $query->whereDate('created_date', '>=', $dateFrom);
        if ($dateTo) $query->whereDate('created_date', '<=', $dateTo);
        if ($partnerId) $query->where('office_partner_id', $partnerId);
        
        // Применяем сортировку
        if ($sortBy) {
            switch ($sortBy) {
                case 'name_asc': $query->orderBy('name', 'asc'); break;
                case 'name_desc': $query->orderBy('name', 'desc'); break;
                case 'created_date_asc': $query->orderBy('created_date', 'desc'); break;
                case 'total_sum_asc': $query->orderBy('total_sum', 'asc'); break;
                case 'total_sum_desc': $query->orderBy('total_sum', 'desc'); break;
                default: $query->orderBy('created_at', 'desc');
            }
        } else {
            // Сортировка по умолчанию
            $query->orderBy('created_at', 'desc');
        }

        // Добавляем подсчет клиентских оценок
        $query->withCount(['ratings as client_ratings_count' => function($query) {
            $query->whereHas('raterUser', function($q) {
                $q->where('status', 'client');
            });
        }]);
        
        // Добавляем среднее значение клиентских оценок
        $query->withAvg(['ratings as client_rating_avg' => function($query) {
            $query->whereHas('raterUser', function($q) {
                $q->where('status', 'client');
            });
        }], 'score');

        $deals = $query->get();

        $statuses = [
            'Ждем ТЗ', 'Планировка', 'Коллажи', 'Визуализация', 'Рабочка/сбор ИП',
            'Проект готов', 'Проект завершен', 'Проект на паузе', 'Возврат',
            'В работе', 'Завершенный', 'На потом', 'Регистрация',
            'Бриф прикриплен', 'Поддержка', 'Активный'
        ];

        $feeds = DealFeed::whereIn('deal_id', $deals->pluck('id'))->get();

        return view('cardinators', compact(
            'deals',
            'title_site',
            'search',
            'status',
            'viewType',
            'statuses',
            'feeds'
        ));
    }

    /**
     * Отображение сделок для пользователя.
     */
    public function dealUser(Request $request)
    {
        $title_site = "Мои сделки | Личный кабинет Экспресс-дизайн";
        $user = Auth::user();

        // Получаем сделки пользователя
        $query = Deal::query();
        
        // Для обычных пользователей показываем только их сделки
        $query->whereHas('users', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        });

        // Фильтрация по статусу
        if ($request->has('status') && !empty($request->status)) {
            $query->where('status', $request->status);
        }
        
        // Поиск по названию
        if ($request->has('search') && !empty($request->search)) {
            $query->where(function($q) use ($request) {
                $search = $request->search;
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('project_number', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }
        
        // Сортировка по умолчанию - сначала новые
        $query->orderBy('created_at', 'desc');
        
        $userDeals = $query->get();
        
        // Добавляем информацию о ссылке на бриф для каждой сделки
        foreach ($userDeals as $deal) {
            // Проверяем наличие брифа
            if ($deal->common) {
                $deal->link = route('common.show', $deal->common->id);
            } elseif ($deal->commercial) {
                $deal->link = route('commercial.show', $deal->commercial->id);
            } else {
                $deal->link = null;
            }
        }

        return view('user', compact('userDeals', 'title_site'));
    }

    /**
     * Обновление сделки
     */
    public function updateDeal(Request $request, $id)
    {
        $deal = Deal::findOrFail($id);
        $user = Auth::user();
        
        // Сохраняем оригинальные значения для логирования
        $original = $deal->getAttributes();
        
        // Получаем валидированные данные
        $validatedData = $request->validate([
            'name' => 'nullable|string|max:255',
            'client_phone' => 'nullable|string|max:20',
            'client_email' => 'nullable|email|max:255',
            'client_city' => 'nullable|string|max:255',
            'status' => 'nullable|string|max:255',
            'coordinator_id' => 'nullable|numeric',
            'office_partner_id' => 'nullable|numeric',
            'architect_id' => 'nullable|numeric',
            'designer_id' => 'nullable|numeric',
            'visualizer_id' => 'nullable|numeric',
            'comment' => 'nullable|string',
            'total_sum' => 'nullable|numeric',
            'package' => 'nullable|string',
            'price_service_option' => 'nullable|string',
            'project_number' => 'nullable|string|max:50',
            'created_date' => 'nullable|date',
            'start_date' => 'nullable|date',
            'payment_date' => 'nullable|date',
            'project_end_date' => 'nullable|date',
            'visualization_link' => 'nullable|url',
            'project_duration' => 'nullable|integer',
            'client_city_id' => 'nullable|string',
            'completion_responsible' => 'nullable|string',
            'rooms_count_pricing' => 'nullable|integer',
            // Файловые поля
            'execution_order_file' => 'nullable|file|max:20000',
            'measurements_file' => 'nullable|file|max:20000',
            'final_floorplan' => 'nullable|file|max:20000',
            'final_collage' => 'nullable|file|max:20000',
            'final_project_file' => 'nullable|file|max:20000',
            'work_act' => 'nullable|file|max:20000',
            'archicad_file' => 'nullable|file|max:20000',
            'contract_attachment' => 'nullable|file|max:20000',
            'avatar_path' => 'nullable|file|max:5000|image',
        ]);
        
        // Убираем поля файлов из массива для обновления
        $fileFields = [
            'execution_order_file', 'measurements_file', 'final_floorplan', 
            'final_collage', 'final_project_file', 'work_act', 
            'archicad_file', 'contract_attachment', 'avatar_path'
        ];
        
        $dataToUpdate = array_diff_key($validatedData, array_flip($fileFields));
        
        // Обновляем данные сделки без файлов
        $deal->update($dataToUpdate);
        
        // Обрабатываем загрузку файлов на Яндекс Диск
        $this->handleYandexDiskFileUploads($request, $deal);
        
        // Обработка загрузки аватара
        if ($request->hasFile('avatar_path')) {
            $avatarFile = $request->file('avatar_path');
            $avatarPath = $avatarFile->store('deal_avatars', 'public');
            $deal->avatar_path = $avatarPath;
            $deal->save();
        }
        
        // Проверяем, изменился ли статус сделки
        $statusChanged = $original['status'] !== $deal->status;
        $changedToCompleted = $statusChanged && $deal->status === 'Проект завершен';
        
        // Логирование изменений
        $this->logDealChanges($deal, $original, $deal->getAttributes());
        
        return response()->json([
            'success' => true, 
            'message' => 'Сделка успешно обновлена',
            'status_changed_to_completed' => $changedToCompleted,
            'deal' => $deal
        ]);
    }
    
    /**
     * Обработка файлов для загрузки на Яндекс Диск
     */
    private function handleYandexDiskFileUploads(Request $request, Deal $deal)
    {
        // Массив соответствия полей файлов и их префиксов
        $fileFieldsMapping = [
            'execution_order_file' => 'Распоряжение на исполнение',
            'measurements_file' => 'Замеры',
            'final_floorplan' => 'Финальная планировка',
            'final_collage' => 'Финальный коллаж',
            'final_project_file' => 'Финальный проект',
            'work_act' => 'Акт выполненных работ',
            'archicad_file' => 'Файл Archicad',
            'contract_attachment' => 'Приложение к договору',
            'plan_final' => 'Планировка финал', // Добавляем поле plan_final
            'chat_screenshot' => 'Скриншот чата', // Добавляем поле chat_screenshot
        ];
        
        // Базовый путь для хранения файлов
        $basePath = config('services.yandex_disk.base_folder', 'dlk_deals');
        $projectFolder = $deal->project_number ? "{$deal->project_number}" : "deal_{$deal->id}";
        $dealFolder = "{$basePath}/{$projectFolder}";
        
        // Обрабатываем каждый файл
        foreach ($fileFieldsMapping as $fieldName => $filePrefix) {
            if ($request->hasFile($fieldName)) {
                $file = $request->file($fieldName);
                $originalName = $file->getClientOriginalName();
                $fileName = Str::slug($filePrefix) . '_' . time() . '_' . $originalName;
                $diskPath = "{$dealFolder}/{$fieldName}/{$fileName}";

                try {
                    // Увеличиваем время ожидания для загрузки файлов
                    $this->yandexDiskService->setTimeout(10000); // 1000 секунд

                    $uploadResult = $this->yandexDiskService->uploadFile($file, $diskPath);

                    if ($uploadResult['success']) {
                        $deal->update([
                            "yandex_url_{$fieldName}" => $uploadResult['url'],
                            "yandex_disk_path_{$fieldName}" => $uploadResult['path'],
                            "original_name_{$fieldName}" => $originalName,
                        ]);

                        Log::info("Файл {$fieldName} успешно загружен на Яндекс.Диск", [
                            'deal_id' => $deal->id,
                            'path' => $diskPath
                        ]);
                    } else {
                        Log::error("Ошибка при загрузке файла {$fieldName} на Яндекс.Диск", [
                            'deal_id' => $deal->id,
                            'error' => $uploadResult['message'] ?? 'Неизвестная ошибка'
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::error("Исключение при загрузке файла {$fieldName} на Яндекс.Диск", [
                        'deal_id' => $deal->id, 
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }
    }

    protected function logDealChanges($deal, $original, $new)
    {
        foreach (['updated_at', 'created_at'] as $key) {
            unset($original[$key], $new[$key]);
        }

        $changes = [];
        foreach ($new as $key => $newValue) {
            if (array_key_exists($key, $original) && $original[$key] != $newValue) {
                $changes[$key] = [
                    'old' => $original[$key],
                    'new' => $newValue,
                ];
            }
        }

        if (!empty($changes)) {
            DealChangeLog::create([
                'deal_id'   => $deal->id,
                'user_id'   => Auth::id(),
                'user_name' => Auth::user()->name,
                'changes'   => $changes,
            ]);
        }
    }

    public function storeDealFeed(Request $request, $dealId)
    {
        $request->validate([
            'content' => 'required|string|max:1990',
        ]);

        $deal = Deal::findOrFail($dealId);
        $user = Auth::user();

        $feed = new DealFeed();
        $feed->deal_id = $deal->id;
        $feed->user_id = $user->id;
        $feed->content = $request->input('content');
        $feed->save();

        return response()->json([
            'user_name'  => $user->name,
            'content'    => $feed->content,
            'date'       => $feed->created_at->format('d.m.Y H:i'),
            'avatar_url' => $user->avatar_url,
        ]);
    }

    /**
     * Форма создания сделки – доступна для координатора, администратора и партнёра.
     */
    public function createDeal()
    {
        $user = Auth::user();
        if (!in_array($user->status, ['coordinator', 'admin', 'partner'])) {
            return redirect()->route('deal.cardinator')
                ->with('error', 'Только координатор, администратор или партнер могут создавать сделку.');
        }
        $title_site = "Создание сделки";

        $citiesFile = public_path('cities.json');
        if (file_exists($citiesFile)) {
            $citiesJson = file_get_contents($citiesFile);
            $russianCities = json_decode($citiesJson, true);
        } else {
            $russianCities = [];
        }

        $coordinators = User::where('status', 'coordinator')->get();
        $partners = User::where('status', 'partner')->get();

        return view('create_deal', compact(
            'title_site',
            'user',
            'coordinators',
            'partners',
            'russianCities'
        ));
    }

    /**
     * Сохранение сделки с автоматическим созданием группового чата для ответственных.
     */
    public function storeDeal(Request $request)
    {
        $validated = $request->validate([
            'name'                    => 'required|string|max:255',
            'client_phone'            => 'required|string|max:50',
            'package'                 => 'required|string|max:255',
            'price_service_option'    => 'required|string|max:255',
            'rooms_count_pricing'     => 'nullable|integer|min:1|max:2147483647',
            'execution_order_comment' => 'nullable|string|max:1000',
            'execution_order_file'    => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'office_partner_id'       => 'nullable|exists:users,id',
            'coordinator_id'          => 'nullable|exists:users,id',
            'total_sum'               => 'nullable|numeric',
            'measuring_cost'          => 'nullable|numeric',
            'client_info'             => 'nullable|string',
            'payment_date'            => 'nullable|date',
            'execution_comment'       => 'nullable|string',
            'comment'                 => 'nullable|string',
            'client_timezone'         => 'nullable|string',
            'completion_responsible'  => 'required|string', // Изменено с nullable на required
            'start_date'              => 'nullable|date',
            'project_duration'        => 'nullable|integer',
            'project_end_date'        => 'nullable|date',
        ]);

        $user = Auth::user(); 
        if (!in_array($user->status, ['coordinator', 'admin', 'partner'])) {
            return redirect()->route('deal.cardinator')
                ->with('error', 'Только координатор, администратор или партнер могут создавать сделку.');
        } 
 
        try {
            $coordinatorId = $validated['coordinator_id'] ?? auth()->id();

            // Нормализация номера телефона клиента для поиска (удаление нецифровых символов)
            $normalizedPhone = preg_replace('/\D/', '', $validated['client_phone']);

            // Поиск существующего пользователя по номеру телефона
            $existingUser = User::where('phone', 'LIKE', '%' . $normalizedPhone . '%')->first();
            
            // Используем ID существующего пользователя или текущего авторизованного пользователя
            // Это гарантирует, что user_id никогда не будет NULL
            $userId = $existingUser ? $existingUser->id : auth()->id();

            $deal = Deal::create([
                'name'                   => $validated['name'],
                'client_phone'           => $validated['client_phone'],
                'status'                 => 'Ждем ТЗ', // устанавливаем значение по умолчанию
                'package'                => $validated['package'],
                'client_name'            => $validated['name'],
                'price_service_option'   => $validated['price_service_option'],
                'rooms_count_pricing'    => $validated['rooms_count_pricing'] ?? null,
                'execution_order_comment'=> $validated['execution_order_comment'] ?? null,
                'office_partner_id'      => $validated['office_partner_id'] ?? null,
                'coordinator_id'         => $coordinatorId,
                'total_sum'              => $validated['total_sum'] ?? null,
                'measuring_cost'         => $validated['measuring_cost'] ?? null,
                'client_info'            => $validated['client_info'] ?? null,
                'payment_date'           => $validated['payment_date'] ?? null,
                'execution_comment'      => $validated['execution_comment'] ?? null,
                'comment'                => $validated['comment'] ?? null,
                'client_timezone'        => $validated['client_timezone'] ?? null,
                'completion_responsible' => $validated['completion_responsible'] ?? null,
                'user_id'                => $userId, // Устанавливаем ID найденного пользователя или текущего
                'registration_token'     => Str::random(32),
                'registration_token_expiry' => now()->addDays(7),
                'start_date'             => $validated['start_date'] ?? null,
                'project_duration'       => $validated['project_duration'] ?? null,
                'project_end_date'       => $validated['project_end_date'] ?? null,
            ]);

            // Загрузка файлов
            $fileFields = [
                'avatar',
                'execution_order_file',
            ];

            foreach ($fileFields as $field) {
                $uploadData = $this->handleFileUpload($request, $deal, $field, $field === 'avatar' ? 'avatar_path' : $field);
                if (!empty($uploadData)) {
                    $deal->update($uploadData);
                }
            }

            // Привязываем текущего пользователя как координатора
            $deal->users()->attach([auth()->id() => ['role' => 'coordinator']]);

            // Формируем массив связей для таблицы deal_user
            $dealUsers = [auth()->id() => ['role' => 'coordinator']];
            if ($request->filled('architect_id') && User::where('id', $request->input('architect_id'))->exists()) {
                $dealUsers[$request->input('architect_id')] = ['role' => 'architect'];
                $deal->architect_id = $request->input('architect_id');
            }
            if ($request->filled('designer_id') && User::where('id', $request->input('designer_id'))->exists()) {
                $dealUsers[$request->input('designer_id')] = ['role' => 'designer'];
                $deal->designer_id = $request->input('designer_id');
            }
            if ($request->filled('visualizer_id') && User::where('id', $request->input('visualizer_id'))->exists()) {
                $dealUsers[$request->input('visualizer_id')] = ['role' => 'visualizer'];
                $deal->visualizer_id = $request->input('visualizer_id');
            }

            // Привязываем существующего клиента, если найден
            if ($existingUser) {
                $dealUsers[$existingUser->id] = ['role' => 'client'];
                // Записываем в лог привязку клиента по номеру телефона
                \Illuminate\Support\Facades\Log::info('Клиент привязан к сделке по номеру телефона', [
                    'deal_id' => $deal->id,
                    'client_id' => $existingUser->id,
                    'client_phone' => $validated['client_phone'],
                    'normalized_phone' => $normalizedPhone
                ]);
            }

            $deal->save();
            $deal->users()->attach($dealUsers);

            // Отправляем смс с регистрационной ссылкой ТОЛЬКО если клиент ещё не зарегистрирован
            if (!$existingUser) {
                $this->sendSmsNotification($deal, $deal->registration_token);
            } else {
                // Для существующего клиента сразу обновляем статус сделки
                $deal->status = 'Регистрация';
                $deal->save();
            }

            // Добавляем клиента в пользователей сделки, если такого клиента нет по email
            if(!empty($deal->client_email)) {
                $clientByEmail = User::where('email', $deal->client_email)->first();
                if($clientByEmail && !$deal->users()->where('user_id', $clientByEmail->id)->exists()) {
                    $deal->users()->attach($clientByEmail->id, ['role' => 'client']);
                }
            }

            return redirect()->route('deal.cardinator')->with('success', 'Сделка успешно создана.');
        } catch (\Exception $e) {
            Log::error("Ошибка при создании сделки: " . $e->getMessage());
            return redirect()->back()->with('error', 'Ошибка при создании сделки: ' . $e->getMessage());
        }
    }

    /**
     * Обработка загрузки файлов.
     */
    private function handleFileUpload(Request $request, $deal, $field, $targetField = null)
    {
        if ($request->hasFile($field) && $request->file($field)->isValid()) {
            // Обрабатываем и "avatar", и "avatar_path" как аватар сделки
            if ($field === 'avatar' || $field === 'avatar_path') {
                $dir = "dels/{$deal->id}"; // Файл сохраняется в папку dels/{id сделки}
                $fileName = "avatar." . $request->file($field)->getClientOriginalExtension(); // Имя файла всегда "avatar"
            } else {
                $dir = "dels/{$deal->id}";
                $fileName = $field . '.' . $request->file($field)->getClientOriginalExtension();
            }
            $filePath = $request->file($field)->storeAs($dir, $fileName, 'public');
            return [$targetField ?? $field => $filePath]; // Для аватара "avatar_path" будет установлен путь сохраненного файла
        }
        return [];
    }

    /**
     * Отправляет SMS-уведомление координатору о смене статуса сделки
     *
     * @param \App\Models\Deal $deal Сделка с обновленным статусом
     * @param string $oldStatus Предыдущий статус сделки
     * @return void
     */
    protected function notifyCoordinatorAboutStatusChange($deal, $oldStatus)
    {
        try {
            // Проверяем наличие координатора
            if (!$deal->coordinator_id) {
                Log::warning("Не удалось отправить SMS: у сделки #{$deal->id} не указан координатор");
                return;
            }
            
            // Получаем данные координатора
            $coordinator = \App\Models\User::find($deal->coordinator_id);
            if (!$coordinator || !$coordinator->phone) {
                Log::warning("Не удалось отправить SMS: у координатора сделки #{$deal->id} нет номера телефона");
                return;
            }
            
            // Формируем сообщение
            $message = "Статус сделки #{$deal->id} изменен c \"{$oldStatus}\" на \"{$deal->status}\". Клиент: {$deal->name}";
            
            // Ограничиваем длину сообщения
            if (strlen($message) > 160) {
                $message = substr($message, 0, 157) . '...';
            }
            
            // Отправляем SMS через сервис
            $smsService = new \App\Services\SmsService();
            $result = $smsService->sendSms($coordinator->phone, $message);
            
            if (!$result) {
                Log::error("Ошибка при отправке SMS координатору {$coordinator->name} ({$coordinator->phone})");
            }
        } catch (\Exception $e) {
            Log::error("Исключение при отправке SMS о смене статуса: " . $e->getMessage());
        }
    }

    /**
     * Отправляет SMS-уведомление клиенту о смене статуса сделки
     *
     * @param \App\Models\Deal $deal Сделка с обновленным статусом
     * @param string $oldStatus Предыдущий статус сделки
     * @return void
     */
    protected function notifyClientAboutStatusChange($deal, $oldStatus)
    {
        try {
            // Проверяем наличие номера телефона клиента
            if (!$deal->client_phone) {
                Log::warning("Не удалось отправить SMS клиенту: у сделки #{$deal->id} не указан телефон клиента");
                return;
            }
            
            // Нормализуем номер телефона клиента для отправки
            $rawPhone = preg_replace('/\D/', '', $deal->client_phone);
            if (strlen($rawPhone) < 10) {
                Log::warning("Не удалось отправить SMS: некорректный номер телефона клиента в сделке #{$deal->id}");
                return;
            }
            
            // Получаем домен сайта из конфигурации
            $domain = config('app.url', 'https://express-design.ru');
            
            // Формируем сообщение
            $message = "Статус вашего проекта изменен с \"{$oldStatus}\" на \"{$deal->status}\". Подробности: {$domain}";
            
            // Ограничиваем длину сообщения
            if (strlen($message) > 160) {
                $message = substr($message, 0, 157) . '...';
            }
            
            // Отправляем SMS через сервис
            $apiKey = config('services.smsru.api_id', '6CDCE0B0-6091-278C-5145-360657FF0F9B');
            $response = Http::get("https://sms.ru/sms/send", [
                'api_id'    => $apiKey,
                'to'        => $rawPhone,
                'msg'       => $message,
                'partner_id'=> 1,
            ]);
            
            if ($response->failed()) {
                Log::error("Ошибка при отправке SMS клиенту для сделки #{$deal->id}. Ответ: " . $response->body());
            } else {
                Log::info("SMS-уведомление о смене статуса отправлено клиенту", [
                    'deal_id' => $deal->id,
                    'phone' => $rawPhone,
                    'new_status' => $deal->status,
                    'old_status' => $oldStatus
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Исключение при отправке SMS клиенту о смене статуса: " . $e->getMessage());
        }
    }

    /**
     * Отправка SMS-уведомления с регистрационной ссылкой.
     */
    private function sendSmsNotification($deal, $registrationToken)
    {
        if (!$registrationToken) {
            Log::error("Отсутствует регистрационный токен для сделки ID: {$deal->id}");
            throw new \Exception('Отсутствует регистрационный токен для сделки.');
        }

        $rawPhone = preg_replace('/\D/', '', $deal->client_phone);
        $registrationLinkUrl = route('register_by_deal', ['token' => $registrationToken]);
        $apiKey = config('services.smsru.api_id', '6CDCE0B0-6091-278C-5145-360657FF0F9B');

        $response = Http::get("https://sms.ru/sms/send", [
            'api_id'    => $apiKey,
            'to'        => $rawPhone,
            'msg'       => "Здравствуйте! Для регистрации пройдите по ссылке: $registrationLinkUrl",
            'partner_id'=> 1,
        ]);

        if ($response->failed()) {
            Log::error("Ошибка при отправке SMS для сделки ID: {$deal->id}. Ответ сервера: " . $response->body());
            throw new \Exception('Ошибка при отправке SMS.');
        }
    }

    /**
     * Отображение логов изменений для конкретной сделки.
     */
    public function changeLogsForDeal($dealId)
    {
        $deal = Deal::findOrFail($dealId);
        $logs = DealChangeLog::where('deal_id', $deal->id)
            ->orderBy('created_at', 'desc')
            ->get();
        $title_site = "Логи изменений сделки";
        return view('deal_change_logs', compact('deal', 'logs', 'title_site'));
    }

    /**
     * Метод для загрузки ленты комментариев по сделке.
     * Вызывается AJAX‑запросом и возвращает JSON с записями ленты.
     */
    public function getDealFeeds($dealId)
    {
        try {
            $deal = Deal::findOrFail($dealId);
            $feeds = $deal->dealFeeds()->with('user')->orderBy('created_at', 'desc')->get();
            $result = $feeds->map(function ($feed) {
                return [
                    'user_name'  => $feed->user->name,
                    'content'    => $feed->content,
                    'date'       => $feed->created_at->format('d.m.Y H:i'),
                    'avatar_url' => $feed->user->avatar_url ? $feed->user->avatar_url : asset('storage/default-avatar.png'),
                ];
            });
            return response()->json($result);
        } catch (\Exception $e) {
            Log::error("Ошибка загрузки ленты: " . $e->getMessage());
            return response()->json(['error' => 'Ошибка загрузки ленты'], 500);
        }
    }
    
    /**
     * Отображение общих логов изменений для всех сделок.
     */
    public function changeLogs()
    {
        $logs = DealChangeLog::with('deal')
            ->orderBy('created_at', 'desc')
            ->paginate(50);
        $title_site = "Логи изменений сделок";
        return view('deals.deal_change_logs', compact('logs', 'title_site'));
    }
}
