<?php

return [
    'base_url' => env('BC_BASE_URL'),

    'username' => env('BC_USERNAME'),

    'password' => env('BC_PASSWORD'),

    'auth_type' => env('BC_AUTH_TYPE', 'NTLM'),

    'company' => env('BC_COMPANY'),

    'timeout' => env('BC_TIMEOUT', 300),

    'connect_timeout' => env('BC_CONNECT_TIMEOUT', 60),

    'api' => [
        'per_page' => env('BC_PER_PAGE', 50),
    ],

    'endpoints' => [
        'customers' => '/api/v2.0/customers', //example endpoint
    ],
];
