<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    */

    'paths' => ['*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => array_filter([
        // Local development
        env('FRONTEND_URL', 'http://localhost:5173'),
        'http://localhost:5173',
        'http://127.0.0.1:5173',
        'http://localhost:5040',
        'http://localhost:5174',
        'http://127.0.0.1:5174',
        
        // Production
        'https://novostroy.org',
        'https://www.novostroy.org',
        'https://admin.novostroy.org',
        'https://auth.novostroy.org',
    ]),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];
