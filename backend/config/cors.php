<?php

return [

    // CORS config
    'paths' => ['api/*', 'graphql', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],
    // NuxtアプリケーションのURLを許可
    'allowed_origins' => ['http://localhost:3000'],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    // 'supports_credentials' => false,
    'supports_credentials' => true,

]; 