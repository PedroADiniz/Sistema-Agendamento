<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

// Regra de negócio do login. Esconde os detalhes do JWT dos controllers.
class AuthService
{
    // tenta logar; devolve token + usuário, ou null se a senha/email estiver errado
    public function login(array $credentials): ?array
    {
        // tenta autenticar e já gera o token
        $token = Auth::guard('api')->attempt($credentials);

        // se não gerou token, as credenciais estão erradas
        if (! $token) {
            return null; // o controller transforma isso em 401
        }

        // pega o usuário que acabou de logar
        $user = Auth::guard('api')->user();

        // monta a resposta padrão (token + dados)
        return $this->tokenPayload($token, $user);
    }

    // devolve o usuário logado no momento
    public function me(): ?User
    {
        return Auth::guard('api')->user();
    }

    // faz logout invalidando o token atual
    public function logout(): void
    {
        Auth::guard('api')->logout();
    }

    // gera um token novo a partir do atual (renovação)
    public function refresh(): array
    {
        // troca o token por um novo
        $token = Auth::guard('api')->refresh();

        // pega o usuário usando o token novo
        $user = Auth::guard('api')->setToken($token)->user();

        return $this->tokenPayload($token, $user);
    }

    // monta o array de resposta da autenticação
    private function tokenPayload(string $token, User $user): array
    {
        return [
            'token' => $token,
            'type' => 'bearer',
            // tempo de expiração em segundos (a config está em minutos, por isso x60)
            'expires_in' => (int) JWTAuth::factory()->getTTL() * 60,
            'user' => $user,
        ];
    }
}
