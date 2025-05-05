<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class YandexDiskService
{
    /**
     * @var string API URL
     */
    protected $apiUrl = 'https://cloud-api.yandex.net/v1/disk';
    
    /**
     * @var Client Guzzle HTTP client
     */
    protected $client;
    
    /**
     * @var string OAuth token
     */
    protected $token;
    
    /**
     * YandexDiskService constructor.
     */
    public function __construct()
    {
        $this->token = config('services.yandex_disk.token');
        $this->client = new Client([
            'headers' => [
                'Authorization' => 'OAuth ' . $this->token,
                'Accept' => 'application/json',
            ],
        ]);
    }
    
    /**
     * Устанавливает таймаут для HTTP-запросов.
     *
     * @param int $seconds
     */
    public function setTimeout(int $seconds)
    {
        $this->client = new Client([
            'base_uri' => $this->apiUrl,
            'timeout' => $seconds,
        ]);
    }
    
    /**
     * Create directory on Yandex Disk, ensuring all parent directories exist
     *
     * @param string $path Path to directory
     * @return bool Whether directory was created successfully
     */
    public function createDirectory($path)
    {
        try {
            if (empty($path)) {
                return false;
            }
            
            // Проверяем существование директории
            $checkResponse = $this->client->get($this->apiUrl . '/resources', [
                'query' => [
                    'path' => $path,
                ],
                'http_errors' => false
            ]);
            
            // Если директория уже существует, возвращаем true
            if ($checkResponse->getStatusCode() == 200) {
                Log::info("Directory already exists: {$path}");
                return true;
            }
            
            // Если ошибка не 404 (Not Found), что-то пошло не так
            if ($checkResponse->getStatusCode() != 404) {
                Log::error("Unexpected error checking directory: {$path}", [
                    'status' => $checkResponse->getStatusCode(),
                    'body' => (string) $checkResponse->getBody()
                ]);
                return false;
            }
            
            // Создаем родительские директории рекурсивно
            $parentDir = dirname($path);
            // Если путь содержит родительскую директорию и это не корень
            if ($parentDir && $parentDir !== '.' && $parentDir !== '/') {
                $parentCreated = $this->createDirectory($parentDir);
                if (!$parentCreated) {
                    Log::error("Failed to create parent directory: {$parentDir}");
                    return false;
                }
            }
            
            // Создаем директорию
            Log::info("Creating directory: {$path}");
            $response = $this->client->put($this->apiUrl . '/resources', [
                'query' => [
                    'path' => $path,
                ],
                'http_errors' => false
            ]);
            
            // 201 - Created, 409 - Already exists
            $success = $response->getStatusCode() == 201 || $response->getStatusCode() == 409;
            
            if (!$success) {
                Log::error("Failed to create directory: {$path}", [
                    'status' => $response->getStatusCode(),
                    'body' => (string) $response->getBody()
                ]);
            } else {
                Log::info("Directory created successfully: {$path}");
            }
            
            return $success;
        } catch (\Exception $e) {
            Log::error('Yandex Disk create directory error: ' . $e->getMessage(), [
                'path' => $path,
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }
    
    /**
     * Upload file to Yandex Disk
     *
     * @param UploadedFile $file File to upload
     * @param string $path Path on Yandex Disk
     * @return array Upload result
     */
    public function uploadFile(UploadedFile $file, $path)
    {
        try {
            // Ensure directory exists with improved error handling
            $directory = dirname($path);
            $directoryCreated = $this->createDirectory($directory);
            
            if (!$directoryCreated) {
                Log::error("Failed to create directory structure for upload", [
                    'directory' => $directory
                ]);
                return ['success' => false, 'message' => "Failed to create directory structure: {$directory}"];
            }
            
            // Get upload URL
            Log::info("Requesting upload URL for: {$path}");
            $response = $this->client->get($this->apiUrl . '/resources/upload', [
                'query' => [
                    'path' => $path,
                    'overwrite' => 'true',
                ],
                'http_errors' => false
            ]);
            
            if ($response->getStatusCode() != 200) {
                $error = json_decode($response->getBody(), true);
                Log::error('Failed to get upload URL', [
                    'path' => $path,
                    'status' => $response->getStatusCode(),
                    'error' => $error
                ]);
                return ['success' => false, 'message' => 'Failed to get upload URL'];
            }
            
            $result = json_decode($response->getBody(), true);
            
            if (isset($result['href'])) {
                // Upload file to the URL
                Log::info("Uploading file to URL: {$result['href']}");
                $uploadClient = new Client();
                $uploadResponse = $uploadClient->put($result['href'], [
                    'body' => fopen($file->getRealPath(), 'r'),
                    'headers' => [
                        'Content-Type' => $file->getMimeType(),
                    ],
                    'http_errors' => false
                ]);
                
                if ($uploadResponse->getStatusCode() == 201 || $uploadResponse->getStatusCode() == 200) {
                    // Publish file to get public link
                    Log::info("File uploaded successfully, publishing file: {$path}");
                    $publicLink = $this->publishFile($path);
                    
                    if (!$publicLink) {
                        Log::warning("Failed to get public link for file: {$path}");
                    } else {
                        Log::info("File published successfully: {$publicLink}");
                    }
                    
                    return [
                        'success' => true,
                        'path' => $path,
                        'url' => $publicLink,
                        'original_name' => $file->getClientOriginalName(),
                    ];
                } else {
                    Log::error('Failed to upload file to Yandex Disk', [
                        'path' => $path,
                        'status' => $uploadResponse->getStatusCode(),
                        'response' => (string) $uploadResponse->getBody()
                    ]);
                }
            } else {
                Log::error('No upload URL in response', [
                    'path' => $path,
                    'result' => $result
                ]);
            }
            
            return ['success' => false, 'message' => 'Failed to upload file'];
        } catch (\Exception $e) {
            Log::error('Yandex Disk upload error: ' . $e->getMessage(), [
                'path' => $path,
                'file' => $file->getClientOriginalName(),
                'trace' => $e->getTraceAsString()
            ]);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Upload multiple files to Yandex Disk
     *
     * @param array $files Array of UploadedFile
     * @param string $directory Directory on Yandex Disk
     * @return array Upload results
     */
    public function uploadFiles($files, $directory)
    {
        $results = [];
        
        // Ensure the directory exists
        $this->createDirectory($directory);
        
        foreach ($files as $file) {
            // Generate a unique filename to avoid collisions
            $filename = uniqid() . '_' . $file->getClientOriginalName();
            $path = $directory . '/' . $filename;
            
            $results[] = $this->uploadFile($file, $path);
        }
        
        return $results;
    }
    
    /**
     * Publish file to get a public link
     *
     * @param string $path Path to the file
     * @return string|bool Public URL or false on failure
     */
    public function publishFile($path)
    {
        try {
            $response = $this->client->put($this->apiUrl . '/resources/publish', [
                'query' => [
                    'path' => $path,
                ],
                'http_errors' => false
            ]);
            
            if ($response->getStatusCode() == 200) {
                // Get the public link of the published file
                $metaResponse = $this->client->get($this->apiUrl . '/resources', [
                    'query' => [
                        'path' => $path,
                    ],
                    'http_errors' => false
                ]);
                
                if ($metaResponse->getStatusCode() == 200) {
                    $meta = json_decode($metaResponse->getBody(), true);
                    
                    // Return the public URL if available
                    if (isset($meta['public_url'])) {
                        return $meta['public_url'];
                    }
                }
            }
            
            return false;
        } catch (\Exception $e) {
            Log::error('Yandex Disk publish error: ' . $e->getMessage(), [
                'path' => $path,
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }
    
    /**
     * Delete a file or directory from Yandex Disk
     *
     * @param string $path Path to the file or directory
     * @return bool Whether deletion was successful
     */
    public function deleteFile($path)
    {
        try {
            $response = $this->client->delete($this->apiUrl . '/resources', [
                'query' => [
                    'path' => $path,
                    'permanently' => 'true',
                ],
                'http_errors' => false
            ]);
            
            return $response->getStatusCode() == 204 || $response->getStatusCode() == 200;
        } catch (\Exception $e) {
            Log::error('Yandex Disk delete error: ' . $e->getMessage(), [
                'path' => $path,
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }
    
    /**
     * Check if file or directory exists on Yandex Disk
     *
     * @param string $path Path to check
     * @return bool Whether file or directory exists
     */
    public function exists($path)
    {
        try {
            $response = $this->client->get($this->apiUrl . '/resources', [
                'query' => [
                    'path' => $path,
                ],
                'http_errors' => false
            ]);
            
            return $response->getStatusCode() == 200;
        } catch (\Exception $e) {
            return false;
        }
    }
}
