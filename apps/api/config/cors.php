<?php

return [
    // Session/cookie auth needs CORS on the API + the CSRF cookie route.
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => explode(',', env('FRONTEND_URLS', 'http://localhost:3000,http://localhost:3001')),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    // Required so the browser sends/stores the session + XSRF-TOKEN cookies.
    'supports_credentials' => true,
];
