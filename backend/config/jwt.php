<?php

/*
 * Configuração do php-open-source-saver/jwt-auth.
 * (equivalente ao publicado por: php artisan vendor:publish --provider="PHPOpenSourceSaver\JWTAuth\Providers\LaravelServiceProvider")
 */

return [
    // Chave secreta usada para assinar os tokens (algoritmos HMAC).
    // Gerada com: php artisan jwt:secret
    'secret' => env('JWT_SECRET'),

    // Par de chaves para algoritmos RSA/ECDSA (não usados por padrão).
    'keys' => [
        'public' => env('JWT_PUBLIC_KEY'),
        'private' => env('JWT_PRIVATE_KEY'),
        'passphrase' => env('JWT_PASSPHRASE'),
    ],

    // Tempo de vida do token em minutos (1h por padrão).
    'ttl' => env('JWT_TTL', 60),

    // Tempo limite para refresh em minutos (2 semanas por padrão).
    'refresh_ttl' => env('JWT_REFRESH_TTL', 20160),

    // Algoritmo de assinatura.
    'algo' => env('JWT_ALGO', 'HS256'),

    'required_claims' => [
        'iss', 'iat', 'exp', 'nbf', 'sub', 'jti',
    ],

    'persistent_claims' => [],

    'lock_subject' => true,

    'leeway' => env('JWT_LEEWAY', 0),

    // Lista de tokens invalidados (logout). Requer um store de cache.
    'blacklist_enabled' => env('JWT_BLACKLIST_ENABLED', true),

    'blacklist_grace_period' => env('JWT_BLACKLIST_GRACE_PERIOD', 0),

    'show_black_list_exception' => env('JWT_SHOW_BLACKLIST_EXCEPTION', true),

    'decrypt_cookies' => false,

    'providers' => [
        'jwt' => PHPOpenSourceSaver\JWTAuth\Providers\JWT\Lcobucci::class,
        'auth' => PHPOpenSourceSaver\JWTAuth\Providers\Auth\Illuminate::class,
        'storage' => PHPOpenSourceSaver\JWTAuth\Providers\Storage\Illuminate::class,
    ],
];
