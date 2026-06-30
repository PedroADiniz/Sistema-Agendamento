<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

// Regra de negócio dos usuários. Conversa com o repositório, não com o banco direto.
class UserService
{
    // recebe o repositório por injeção de dependência
    public function __construct(
        private readonly UserRepositoryInterface $users
    ) {}

    // devolve a lista de usuários
    public function list(): Collection
    {
        return $this->users->all();
    }

    // busca um usuário pelo id; se não achar, dispara 404
    public function find(int $id): User
    {
        $user = $this->users->findById($id);

        // se veio null, para tudo e retorna 404
        abort_if($user === null, 404, 'Usuário não encontrado.');

        return $user;
    }

    // cria um usuário novo (a senha vira hash sozinha pelo cast do model)
    public function create(array $data): User
    {
        return $this->users->create([
            'name' => $data['name'] ?? null,
            'email' => $data['email'],
            'role' => $data['role'] ?? 'atendente', // se não informar, vira atendente
            'password' => $data['password'],
        ]);
    }

    // atualiza um usuário. E-mail e senha NÃO podem ser alterados (regra RQF1.3).
    public function update(User $user, array $data): User
    {
        // monta só os campos editáveis (nome e tipo); e-mail/senha ficam de fora
        $payload = array_filter(
            [
                'name' => $data['name'] ?? $user->name,
                'role' => $data['role'] ?? ($user->role->value ?? $user->role),
            ],
            fn ($v) => $v !== null
        );

        return $this->users->update($user, $payload);
    }

    // apaga um usuário
    public function delete(User $user): bool
    {
        return $this->users->delete($user);
    }
}
