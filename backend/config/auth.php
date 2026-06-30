<?php

return [
    // Guard padrão da aplicação. Usamos "api" (JWT) por ser uma API stateless.
    'defaults' => [
        'guard' => env('AUTH_GUARD', 'api'),
        'passwords' => env('AUTH_PASSWORD_BROKER', 'users'),
    ],

    'guards' => [
        // Guard web tradicional (mantido para compatibilidade; não usado pela API).
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],

        // Guard da API: driver "jwt" do pacote php-open-source-saver/jwt-auth.
        // É ele que o middleware auth:api utiliza.
        'api' => [
            'driver' => 'jwt',
            'provider' => 'users',
        ],
    ],

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => env('AUTH_MODEL', App\Models\User::class),
        ],
    ],

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens'),
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),
];
