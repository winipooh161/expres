<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Services\YandexDiskService;
use Illuminate\Support\Facades\Log;

class Commercial extends Model
{
    use HasFactory;

    protected $fillable = [
        'id', 'title', 'article', 'description', 'current_page', 'status', 'price', 
        'user_id', 'zones', 'total_area', 'projected_area', 'deal_id', 
        'documents', 'references', 'preferences', 'zone_budgets'
    ];

    protected $casts = [
        'zones' => 'array',
        'preferences' => 'array',
        'zone_budgets' => 'array',
        'documents' => 'array',
        'references' => 'array'
    ];

    // Связь с сделками
    public function deals()
    {
        return $this->hasMany(Deal::class, 'commercial_id');
    }

    // Связь с координатором
    public function coordinator()
    {
        return $this->belongsTo(User::class, 'coordinator_id');
    }

    // Связь с автором
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    
    /**
     * Загружает документы на Яндекс.Диск
     *
     * @param array $files Массив файлов для загрузки
     * @return array Результаты загрузки
     */
    public function uploadDocuments($files)
    {
        try {
            $yandexDiskService = app(YandexDiskService::class);
            $uploadPath = "commercials/{$this->id}/documents";
            
            // Логируем начало загрузки
            Log::info('Начало загрузки документов для коммерческого брифа', [
                'commercial_id' => $this->id,
                'files_count' => count($files),
                'upload_path' => $uploadPath
            ]);
            
            // Загружаем файлы
            $results = $yandexDiskService->uploadFiles($files, $uploadPath);
            
            // Получаем существующие документы
            $existingDocuments = $this->documents ? json_decode($this->documents, true) : [];
            if (!is_array($existingDocuments)) {
                $existingDocuments = [];
            }
            
            // Обрабатываем результаты загрузки
            foreach ($results as $result) {
                if (isset($result['success']) && $result['success'] && !empty($result['url'])) {
                    $existingDocuments[] = $result['url'];
                    Log::info('Документ успешно загружен', [
                        'commercial_id' => $this->id,
                        'url' => $result['url']
                    ]);
                } else {
                    Log::error('Загрузка документа не удалась', [
                        'commercial_id' => $this->id,
                        'result' => $result
                    ]);
                }
            }
            
            // Сохраняем обновленные документы
            $this->documents = json_encode(array_values(array_filter($existingDocuments)));
            $this->save();
            
            Log::info('Загрузка документов завершена', [
                'commercial_id' => $this->id,
                'documents_count' => count($existingDocuments)
            ]);
            
            return $results;
        } catch (\Exception $e) {
            Log::error('Исключение при загрузке документов', [
                'commercial_id' => $this->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Загружает референсы на Яндекс.Диск
     *
     * @param array $files Массив файлов для загрузки
     * @return array Результаты загрузки
     */
    public function uploadReferences($files)
    {
        try {
            $yandexDiskService = app(YandexDiskService::class);
            $uploadPath = "commercials/{$this->id}/references";
            
            // Логируем начало загрузки
            Log::info('Начало загрузки референсов для коммерческого брифа', [
                'commercial_id' => $this->id,
                'files_count' => count($files),
                'upload_path' => $uploadPath
            ]);
            
            // Загружаем файлы
            $results = $yandexDiskService->uploadFiles($files, $uploadPath);
            
            // Получаем существующие референсы
            $existingReferences = $this->references ? json_decode($this->references, true) : [];
            if (!is_array($existingReferences)) {
                $existingReferences = [];
            }
            
            // Обрабатываем результаты загрузки
            foreach ($results as $result) {
                if (isset($result['success']) && $result['success'] && !empty($result['url'])) {
                    $existingReferences[] = $result['url'];
                    Log::info('Референс успешно загружен', [
                        'commercial_id' => $this->id,
                        'url' => $result['url']
                    ]);
                } else {
                    Log::error('Загрузка референса не удалась', [
                        'commercial_id' => $this->id,
                        'result' => $result
                    ]);
                }
            }
            
            // Сохраняем обновленные референсы
            $this->references = json_encode(array_values(array_filter($existingReferences)));
            $this->save();
            
            Log::info('Загрузка референсов завершена', [
                'commercial_id' => $this->id,
                'references_count' => count($existingReferences)
            ]);
            
            return $results;
        } catch (\Exception $e) {
            Log::error('Исключение при загрузке референсов', [
                'commercial_id' => $this->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Удаляет файл с Яндекс.Диска
     *
     * @param string $fileUrl URL файла
     * @return bool Успешно ли удален файл
     */
    public function deleteFileFromYandexDisk($fileUrl)
    {
        $yandexDiskService = app(YandexDiskService::class);
        
        // Получаем путь из URL
        $path = $this->getPathFromUrl($fileUrl);
        
        $success = $yandexDiskService->deleteFile($path);
        
        if ($success) {
            // Обновляем список файлов
            if ($this->references) {
                $references = json_decode($this->references, true);
                $references = array_filter($references, function($url) use ($fileUrl) {
                    return $url !== $fileUrl;
                });
                $this->references = json_encode($references);
            }
            
            if ($this->documents) {
                $documents = json_decode($this->documents, true);
                $documents = array_filter($documents, function($url) use ($fileUrl) {
                    return $url !== $fileUrl;
                });
                $this->documents = json_encode($documents);
            }
            
            $this->save();
        }
        
        return $success;
    }
    
    /**
     * Извлекает путь из URL файла
     *
     * @param string $url URL файла
     * @return string Путь к файлу
     */
    protected function getPathFromUrl($url)
    {
        $parsed = parse_url($url);
        if (isset($parsed['query'])) {
            parse_str($parsed['query'], $query);
            if (isset($query['path'])) {
                return $query['path'];
            }
        }
        
        return $url;
    }
}
