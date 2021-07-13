<?php

return [
    'source' => [
        'site_url' => env('SOURCE_SITE_URL', ''),
        'key' => env('SOURCE_KEY', ''),
        'secret' => env('SOURCE_SECRET', ''),
        'version' => env('SOURCE_API_VERSION', 'wc/v3'),
        'timeout' => env('SOURCE_REQUEST_TIMEOUT', 10000),
    ],
    'destination' => [
        'site_url' => env('DESTINATION_SITE_URL', ''),
        'key' => env('DESTINATION_KEY', ''),
        'secret' => env('DESTINATION_SECRET', ''),
        'version' => env('DESTINATION_API_VERSION', 'wc/v3'),
        'timeout' => env('DESTINATION_REQUEST_TIMEOUT', 10000),
        'nonce' => env('DESTINATION_NONCE', ''),
        'cookie' => env('DESTINATION_COOKIE', '')
    ]
];
