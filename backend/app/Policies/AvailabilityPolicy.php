<?php

namespace App\Policies;

use App\Models\Availability;
use App\Models\User;

// Regras de quem pode mexer nas disponibilidades.
// Só admin cria/edita/exclui; todos podem ver.
class AvailabilityPolicy
{
    // todos os logados podem ver a lista
    public function viewAny(User $actor): bool
    {
        return true;
    }

    // só admin cria
    public function create(User $actor): bool
    {
        return $actor->isAdmin();
    }

    // só admin edita
    public function update(User $actor, Availability $availability): bool
    {
        return $actor->isAdmin();
    }

    // só admin exclui
    public function delete(User $actor, Availability $availability): bool
    {
        return $actor->isAdmin();
    }
}
