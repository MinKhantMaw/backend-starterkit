<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => env('FRONTEND_URLS')
        ? explode(',', env('FRONTEND_URLS'))
        : ['http://localhost:5173'],

    'allowed_headers' => ['*'],

    'supports_credentials' => true,
];
