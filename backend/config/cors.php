<?php

return [

    /*graphqlエンドポイント設定*/ 
    'paths' => ['api/*', 'graphql', 'graphql-playground'],

    'allowed_methods' => ['*'],
    // NuxtアプリケーションのURLを許可
    'allowed_origins' => ['http://localhost:3000'],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => false,

]; 