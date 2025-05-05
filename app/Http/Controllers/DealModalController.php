<?php

namespace App\Http\Controllers;

use App\Models\Deal;
use App\Models\DealFeed;
use App\Models\User;
use App\Models\ChatGroup;
use Illuminate\Support\Facades\Log;

class DealModalController extends Controller
{
    /**
     * Отображение модального окна для сделки.
     */
    public function getDealModal($id)
    {
        try {
            $deal = Deal::with(['coordinator', 'responsibles', 'users'])->findOrFail($id);
            $feeds = DealFeed::where('deal_id', $id)
                ->with('user')
                ->orderBy('created_at', 'desc')
                ->get();

            // Получаем групповой чат для сделки, если он существует
            $groupChat = null;
            if ($deal->chat_group_id) {
                $groupChat = ChatGroup::find($deal->chat_group_id);
            }
    
            // Формирование полей сделки
            $dealFields = $this->getDealFields();

            // Добавляем переменную page в представление 
            $page = 'deals';

            return response()->json([
                'success' => true,
                'html' => view('deals.partials.dealModal', compact('deal', 'feeds', 'dealFields', 'groupChat', 'page'))->render()
            ]);
        } catch (\Exception $e) {
            Log::error("Ошибка отображения модального окна сделки: " . $e->getMessage(), ['exception' => $e]);
            return response()->json(['success' => false, 'error' => 'Ошибка при загрузке данных сделки: ' . $e->getMessage()], 500);
        }
    }

    private function getDealFields() {
        // Получаем только необходимые списки пользователей для полей
        $coordinators = User::where('status', 'coordinator')->pluck('name', 'id')->toArray();
        $partners = User::where('status', 'partner')->pluck('name', 'id')->toArray();
        $architects = User::where('status', 'architect')->pluck('name', 'id')->toArray();
        $designers = User::where('status', 'designer')->pluck('name', 'id')->toArray();
        $visualizers = User::where('status', 'visualizer')->pluck('name', 'id')->toArray();
        
        return [
            'zakaz' => [
                [
                    'name' => 'project_number',
                    'label' => '№ проекта',
                    'type' => 'text',
                    'role' => ['coordinator', 'admin'],
                    'maxlength' => 21,
                    'icon' => 'fas fa-hashtag',
                ],
                [
                    'name' => 'avatar_path',
                    'label' => 'Аватар сделки',
                    'type' => 'file',
                    'role' => ['coordinator', 'admin'],
                    'accept' => 'image/*',
                    'icon' => 'fas fa-image',
                ],
                [
                    'name' => 'status',
                    'label' => 'Статус',
                    'type' => 'select',
                    'role' => ['coordinator', 'admin'],
                    'options' => [
                        'Ждем ТЗ' => 'Ждем ТЗ',
                        'Планировка' => 'Планировка',
                        'Коллажи' => 'Коллажи',
                        'Визуализация' => 'Визуализация',
                        'Рабочка/сбор ИП' => 'Рабочка/сбор ИП',
                        'Проект готов' => 'Проект готов',
                        'Проект завершен' => 'Проект завершен',
                        'Проект на паузе' => 'Проект на паузе',
                        'Возврат' => 'Возврат',
                        'Регистрация' => 'Регистрация',
                        'Бриф прикриплен' => 'Бриф прикриплен',
                    ],
                    'icon' => 'fas fa-tag',
                ],
                [
                    'name' => 'coordinator_id',
                    'label' => 'Координатор',
                    'type' => 'select',
                    'role' => ['coordinator', 'admin'],
                    'options' => $coordinators,
                    'icon' => 'fas fa-user-tie',
                ],[
                    'name' => 'client_city',
                    'label' => 'Город',
                    'type' => 'select',
                    'role' => ['coordinator', 'admin'],
                    'options' => [],
                    'icon' => 'fas fa-city',
                ],
                [
                    'name' => 'office_partner_id',
                    'label' => 'Партнер',
                    'type' => 'select',
                    'role' => ['coordinator', 'admin'],
                    'options' => $partners,
                    'icon' => 'fas fa-handshake',
                ],
                
                [
                    'name' => 'package',
                    'label' => 'Пакет',
                    'type' => 'select',
                    'role' => ['coordinator', 'admin', 'partner'],
                    'options' => [
                        '1' => '1',
                        '2' => '2',
                        '3' => '3',
                    ],
                    'icon' => 'fas fa-box',
                ],
               
                [
                    'name' => 'price_service_option',
                    'label' => 'Услуга по прайсу',
                    'type' => 'select',
                    'role' => ['coordinator', 'admin', 'partner'],
                    'options' => [
                        'экспресс планировка' => 'Экспресс планировка',
                        'экспресс планировка с коллажами' => 'Экспресс планировка с коллажами',
                        'экспресс проект с электрикой' => 'Экспресс проект с электрикой',
                        'экспресс планировка с электрикой и коллажами' => 'Экспресс планировка с электрикой и коллажами',
                        'экспресс проект с электрикой и визуализацией' => 'Экспресс проект с электрикой и визуализацией',
                        'экспресс рабочий проект' => 'Экспресс рабочий проект',
                        'экспресс эскизный проект с рабочей документацией' => 'Экспресс эскизный проект с рабочей документацией',
                        'экспресс 3Dвизуализация' => 'Экспресс 3Dвизуализация',
                        'экспресс полный дизайн-проект' => 'Экспресс полный дизайн-проект',
                        '360 градусов' => '360 градусов',
                    ],
                    'required' => true,
                    'icon' => 'fas fa-list-check',
                ],
                [
                    'name' => 'rooms_count_pricing',
                    'label' => 'Кол-во комнат по прайсу',
                    'type' => 'number',
                    'role' => ['coordinator', 'admin'],
                    'icon' => 'fas fa-door-open',
                ],
             
                [
                    'name' => 'name',
                    'label' => 'ФИО клиента',
                    'type' => 'text',
                    'id'   => 'nameField',
                    'role' => ['coordinator', 'admin'],
                    'required' => true,
                    'icon' => 'fas fa-user',
                ],
                [
                    'name' => 'client_phone',
                    'label' => 'Телефон',
                    'type' => 'text',
                    'role' => ['coordinator', 'admin'],
                    'required' => true,
                    'icon' => 'fas fa-phone',
                ],
                
                [
                    'name' => 'completion_responsible',
                    'label' => 'Кто делает комплектацию',
                    'type' => 'select',
                    'role' => ['coordinator', 'admin'],
                    'options' => [
                        'клиент' => 'Клиент',
                        'партнер' => 'Партнер',
                        'шопинг-лист' => 'Шопинг-лист',
                        'закупки и снабжение от УК' => 'Нужны закупки и снабжение от УК',
                    ],
                    'icon' => 'fas fa-clipboard-check',
                ],
                [
                    'name' => 'created_date',
                    'label' => 'Дата создания сделки',
                    'type' => 'date',
                    'role' => ['coordinator', 'admin'],
                    'icon' => 'fas fa-calendar-plus',
                ],
                [
                    'name' => 'payment_date',
                    'label' => 'Дата оплаты',
                    'type' => 'date',
                    'role' => ['coordinator', 'admin'],
                    'icon' => 'fas fa-money-check',
                ],
                [
                    'name' => 'total_sum',
                    'label' => 'Сумма заказа',
                    'type' => 'number',
                    'role' => ['coordinator', 'admin'],
                    'step' => '0.01',
                    'icon' => 'fas fa-ruble-sign',
                ],
                [
                    'name' => 'comment',
                    'label' => 'Общий комментарий',
                    'type' => 'textarea',
                    'role' => ['coordinator', 'admin'],
                    'icon' => 'fas fa-sticky-note',
                ],
                [
                    'name' => 'measurements_file',
                    'label' => 'Замеры',
                    'type' => 'file',
                    'role' => ['coordinator', 'admin'],
                    'accept' => '.pdf,.dwg,image/*',
                    'icon' => 'fas fa-ruler-combined',
                ],
            ],
            'rabota' => [
                [
                    'name' => 'start_date',
                    'label' => 'Дата старта работы по проекту',
                    'type' => 'date',
                    'role' => ['coordinator', 'admin', 'partner'],
                    'icon' => 'fas fa-play',
                ],
                [
                    'name' => 'project_duration',
                    'label' => 'Общий срок проекта (в рабочих днях)',
                    'type' => 'number',
                    'role' => ['coordinator', 'admin', 'partner'],
                    'icon' => 'fas fa-hourglass-half',
                ],
                [
                    'name' => 'project_end_date',
                    'label' => 'Дата завершения',
                    'type' => 'date',
                    'role' => ['coordinator', 'admin', 'partner'],
                    'icon' => 'fas fa-flag-checkered',
                ], [
                    'name' => 'visualizer_id',
                    'label' => 'Визуализатор',
                    'type' => 'select',
                    'role' => ['coordinator', 'admin'],
                    'options' => $visualizers,
                    'icon' => 'fas fa-eye',
                ],
                [
                    'name' => 'architect_id',
                    'label' => 'Архитектор',
                    'type' => 'select',
                    'role' => ['coordinator', 'admin'],
                    'options' => $architects,
                    'icon' => 'fas fa-drafting-compass',
                ], [
                    'name' => 'designer_id',
                    'label' => 'Дизайнер',
                    'type' => 'select',
                    'role' => ['coordinator', 'admin'],
                    'options' => $designers,
                    'icon' => 'fas fa-palette',
                ],
                [
                    'name' => 'plan_final',
                    'label' => 'Планировка финал (PDF, до 20мб)',
                    'type' => 'file',
                    'role' => ['coordinator', 'admin'],
                    'accept' => 'application/pdf',
                    'icon' => 'fas fa-map',
                ],
               
                [
                    'name' => 'final_collage',
                    'label' => 'Коллаж финал (PDF, до 200мб)',
                    'type' => 'file',
                    'role' => ['coordinator', 'admin'],
                    'accept' => 'application/pdf',
                    'icon' => 'fas fa-object-group',
                ],
                [
                    'name' => 'final_project_file',
                    'label' => 'Финал проекта (PDF, до 200мб)',
                    'type' => 'file',
                    'role' => ['coordinator', 'admin'],
                    'accept' => 'application/pdf',
                    'icon' => 'fas fa-file-pdf',
                ],
               
                [
                    'name' => 'visualization_link',
                    'label' => 'Ссылка на визуализацию',
                    'type' => 'url',
                    'role' => ['coordinator', 'admin', 'partner'],
                    'icon' => 'fas fa-link',
                ],
            ],
            'final' => [
                [
                    'name' => 'work_act',
                    'label' => 'Акт выполненных работ (PDF)',
                    'type' => 'file',
                    'role' => ['coordinator', 'admin'],
                    'accept' => 'application/pdf',
                    'icon' => 'fas fa-file-signature',
                ],
                [
                    'name' => 'chat_screenshot',
                    'label' => 'Скрин чата с оценкой и актом (JPEG)',
                    'type' => 'file',
                    'role' => ['coordinator', 'admin'],
                    'accept' => 'image/jpeg,image/jpg,image/png',
                    'icon' => 'fas fa-camera',
                ],
                [
                    'name' => 'archicad_file',
                    'label' => 'Исходный файл архикад (pln, dwg)',
                    'type' => 'file',
                    'role' => ['coordinator', 'admin'],
                    'accept' => '.pln,.dwg',
                    'icon' => 'fas fa-file-code',
                ],
            ],
        ];
    }
}