<?php

namespace App\Repositories\Contracts;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

// Contrato do repositório de usuários.
// Os Services dependem desta interface, não do Eloquent direto.
interface UserRepositoryInterface
{
    // lista todos os usuários
    public function all(): Collection;

    // busca um usuário pelo id (ou null se não achar)
    public function findById(int $id): ?User;

    // cria um usuário
    public function create(array $data): User;

    // atualiza um usuário
    public function update(User $user, array $data): User;

    // remove um usuário (soft delete)
    public function delete(User $user): bool;
}
