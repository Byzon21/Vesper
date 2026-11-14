<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Politique de sécurité HTTP / anti-bot
    |--------------------------------------------------------------------------
    */
    'rate_limit' => [
        'per_ip' => (int) env('RATE_LIMIT_PER_IP', 120),
        'per_user' => (int) env('RATE_LIMIT_PER_USER', 300),
    ],

    'hmac' => [
        'secret_id' => env('HMAC_SECRET_ID', 'vesper/hmac/signing-key'),
        'tolerance_seconds' => (int) env('HMAC_TOLERANCE_SECONDS', 300),
    ],

    'hsts' => [
        'enabled' => (bool) env('HSTS_ENABLE', true),
        'max_age' => (int) env('HSTS_MAX_AGE', 31536000),
        'include_subdomains' => (bool) env('HSTS_INCLUDE_SUBDOMAINS', true),
    ],

];
