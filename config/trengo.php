<?php

return [
    'token' => env('TRENGO_TOKEN', null),
    'baseURL' => env('TRENGO_BASE_URL', 'https://app.trengo.com/api/v2/'),
    'rateLimitPerMinute' => 120,
];
