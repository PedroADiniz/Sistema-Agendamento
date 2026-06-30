<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

// Implementação do repositório de usuários usando Eloquent
class UserRepository implements UserRepositoryInterface
{
    // busca todos os usuários ordenados pelo nome
    public function all(): Collection
    {
        return User::query()->orderBy('name')->get();
    }

    // busca um usuário pelo id
    public function findById(int $id): ?User
    {
        return User::find($id);
    }

    // salva um usuário novo no banco
    public function create(array $data): User
    {
        return User::create($data);
    }

    // atualiza os dados do usuário e devolve ele atualizado
    public function update(User $user, array $data): User
    {
        $user->update($data);

        return $user->refresh();
    }

    // apaga o usuário (soft delete: só marca como deletado)
    public function delete(User $user): bool
    {
        return (bool) $user->delete();
    }
}
