<?php

return [
    'token' => env('TRENGO_TOKEN'),
    'baseURL' => env('TRENGO_BASE_URL', 'https://app.trengo.com/api/v2/'),
    'rateLimitPerMinute' => 120,
    'channels_id' => [
        'email' => env('TRENGO_EMAIL_CHANNEL', 901613),
    ],
];
