<?php

namespace App\Repositories\Eloquent;

use App\Models\Availability;
use App\Repositories\Contracts\AvailabilityRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

// Implementação do repositório de disponibilidades usando Eloquent
class AvailabilityRepository implements AvailabilityRepositoryInterface
{
    // todas as disponibilidades, já trazendo o atendente, ordenadas
    public function all(): Collection
    {
        return Availability::with('user')
            ->orderBy('user_id')
            ->orderBy('weekday')
            ->orderBy('start_time')
            ->get();
    }

    // disponibilidades de um atendente específico
    public function forUser(int $userId): Collection
    {
        return Availability::where('user_id', $userId)
            ->orderBy('weekday')
            ->orderBy('start_time')
            ->get();
    }

    // só as janelas ativas do atendente naquele dia da semana
    public function activeForUserAndWeekday(int $userId, int $weekday): Collection
    {
        return Availability::where('user_id', $userId)
            ->where('weekday', $weekday)
            ->where('active', true)
            ->orderBy('start_time')
            ->get();
    }

    // busca uma disponibilidade pelo id
    public function findById(int $id): ?Availability
    {
        return Availability::find($id);
    }

    // salva uma disponibilidade nova
    public function create(array $data): Availability
    {
        return Availability::create($data);
    }

    // atualiza e devolve a disponibilidade atualizada
    public function update(Availability $availability, array $data): Availability
    {
        $availability->update($data);

        return $availability->refresh();
    }

    // remove a disponibilidade
    public function delete(Availability $availability): bool
    {
        return (bool) $availability->delete();
    }
}
