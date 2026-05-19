<?php

return [

    'paths' => ['api/*'],

    'allowed_methods' => ['POST', 'OPTIONS'],

    'allowed_origins' => env('TRACKER_ALLOWED_ORIGIN', '*') === '*'
        ? ['*']
        : [env('TRACKER_ALLOWED_ORIGIN')],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];
