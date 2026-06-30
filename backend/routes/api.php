<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AvailabilityController;
use App\Http\Controllers\ScheduleController;

// Todas as rotas aqui já começam com /api (definido no bootstrap/app.php)

// rota pública: login
Route::post('/login', [AuthController::class, 'login']);

// rota "login" nomeada: o Laravel a usa quando alguém acessa sem token.
// como é API, devolvemos 401 em JSON em vez de redirecionar.
Route::get('/login', fn () => \App\Support\ApiResponse::error(
    'Não autenticado. Faça login para continuar.', 401
))->name('login');

// daqui pra baixo, tudo exige token JWT válido (middleware auth:api)
Route::middleware('auth:api')->group(function () {

    // autenticação
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);

    // usuários (a permissão fina fica nas Policies)
    Route::get('/users', [UserController::class, 'index']);
    Route::post('/users', [UserController::class, 'store']);
    Route::get('/users/{user}', [UserController::class, 'show']);
    Route::match(['put', 'patch'], '/users/{user}', [UserController::class, 'update']);
    Route::delete('/users/{user}', [UserController::class, 'destroy']);

    // disponibilidade dos atendentes
    Route::get('/availabilities', [AvailabilityController::class, 'index']);
    Route::post('/availabilities', [AvailabilityController::class, 'store']);
    Route::match(['put', 'patch'], '/availabilities/{availability}', [AvailabilityController::class, 'update']);
    Route::delete('/availabilities/{availability}', [AvailabilityController::class, 'destroy']);

    // consulta de horários livres e criação de agendamento
    Route::get('/schedule/available', [ScheduleController::class, 'available']);
    Route::get('/schedule/day', [ScheduleController::class, 'day']);
    Route::post('/appointments', [ScheduleController::class, 'store']);
});
