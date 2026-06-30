<?php

namespace App\Policies;

use App\Models\User;

// Regras de quem pode fazer o quê com usuários.
// Retorna true/false; quando false, o controller devolve 403.
class UserPolicy
{
    // todos os logados podem ver a lista
    public function viewAny(User $actor): bool
    {
        return true;
    }

    // todos podem ver um usuário
    public function view(User $actor, User $model): bool
    {
        return true;
    }

    // só admin pode criar usuário
    public function create(User $actor): bool
    {
        return $actor->isAdmin();
    }

    // admin edita qualquer um; atendente edita só ele mesmo
    public function update(User $actor, User $model): bool
    {
        return $actor->isAdmin() || $actor->id === $model->id;
    }

    // só admin exclui, e não pode excluir a si mesmo
    public function delete(User $actor, User $model): bool
    {
        return $actor->isAdmin() && $actor->id !== $model->id;
    }
}
