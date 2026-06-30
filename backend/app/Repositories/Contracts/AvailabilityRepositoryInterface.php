<?php

namespace App\Repositories\Contracts;

use App\Models\Availability;
use Illuminate\Database\Eloquent\Collection;

// Contrato do repositório de disponibilidades
interface AvailabilityRepositoryInterface
{
    // lista todas as disponibilidades
    public function all(): Collection;

    // lista as disponibilidades de um atendente
    public function forUser(int $userId): Collection;

    // lista as janelas ATIVAS de um atendente num dia da semana (usado no cálculo dos horários)
    public function activeForUserAndWeekday(int $userId, int $weekday): Collection;

    // busca uma disponibilidade pelo id
    public function findById(int $id): ?Availability;

    // cria uma disponibilidade
    public function create(array $data): Availability;

    // atualiza uma disponibilidade
    public function update(Availability $availability, array $data): Availability;

    // remove uma disponibilidade
    public function delete(Availability $availability): bool;
}
