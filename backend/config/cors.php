<?php

return [
    // Aplica CORS às rotas de API e ao endpoint de health-check.
    'paths' => ['api/*', 'up'],

    'allowed_methods' => ['*'],

    // Origem do frontend (Vite). Configurável via FRONTEND_URL.
    'allowed_origins' => [
        env('FRONTEND_URL', 'http://localhost:5173'),
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    // Como usamos JWT no header Authorization (e não cookies), false é suficiente.
    'supports_credentials' => false,
];
