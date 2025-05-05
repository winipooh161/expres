<?php
return [
    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */
    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],
    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],
    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],
    /*
    |--------------------------------------------------------------------------
    | Яндекс Диск
    |--------------------------------------------------------------------------
    |
    | Настройки для работы с Яндекс.Диском
    |
    */
    'yandex_disk' => [
        'token' => env('YANDEX_DISK_TOKEN', 'y0__xD-1-GlqveAAhjblgMgy8zl_BIVhC5iLWbQTnJiXBfnjmS39_7EUA'), // Убедитесь, что токен указан в .env файле
    ],
    'smsru' => [
        'api_key' => env('SMSRU_API_KEY', '6CDCE0B0-6091-278C-5145-360657FF0F9B'),
    ],
];
