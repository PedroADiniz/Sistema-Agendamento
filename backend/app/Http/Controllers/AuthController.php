<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

// Controller do login. Só organiza: valida, chama o service e responde.
class AuthController extends Controller
{
    // recebe o service por injeção de dependência
    public function __construct(
        private readonly AuthService $auth
    ) {}

    // POST /api/login -> faz login e devolve o token
    public function login(LoginRequest $request): JsonResponse
    {
        // tenta logar com os dados já validados
        $result = $this->auth->login($request->validated());

        // se voltou null, a senha/email está errada -> 401
        if ($result === null) {
            return ApiResponse::error('Credenciais inválidas.', 401);
        }

        // deu certo: devolve token + dados do usuário
        return ApiResponse::success([
            'token' => $result['token'],
            'token_type' => $result['type'],
            'expires_in' => $result['expires_in'],
            'user' => new UserResource($result['user']),
        ], 'Login realizado com sucesso.', 200);
    }

    // GET /api/me -> devolve o usuário logado
    public function me(): JsonResponse
    {
        $user = $this->auth->me();

        return ApiResponse::success(
            new UserResource($user),
            'Usuário autenticado.'
        );
    }

    // POST /api/logout -> invalida o token atual
    public function logout(): JsonResponse
    {
        $this->auth->logout();

        return ApiResponse::success(null, 'Logout realizado com sucesso.');
    }

    // POST /api/refresh -> gera um token novo
    public function refresh(): JsonResponse
    {
        $result = $this->auth->refresh();

        return ApiResponse::success([
            'token' => $result['token'],
            'token_type' => $result['type'],
            'expires_in' => $result['expires_in'],
            'user' => new UserResource($result['user']),
        ], 'Token renovado com sucesso.');
    }
}
