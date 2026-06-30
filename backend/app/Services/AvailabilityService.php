<?php

namespace App\Services;

use App\Models\Availability;
use App\Repositories\Contracts\AvailabilityRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

// Regra de negócio das disponibilidades dos atendentes
class AvailabilityService
{
    // recebe o repositório por injeção de dependência
    public function __construct(
        private readonly AvailabilityRepositoryInterface $availabilities
    ) {}

    // lista as disponibilidades; se passar userId, filtra por atendente
    public function list(?int $userId = null): Collection
    {
        return $userId
            ? $this->availabilities->forUser($userId)
            : $this->availabilities->all();
    }

    // busca uma disponibilidade pelo id; se não achar, dispara 404
    public function find(int $id): Availability
    {
        $availability = $this->availabilities->findById($id);

        abort_if($availability === null, 404, 'Disponibilidade não encontrada.');

        return $availability;
    }

    // cria uma disponibilidade nova
    public function create(array $data): Availability
    {
        return $this->availabilities->create($data);
    }

    // atualiza uma disponibilidade
    public function update(Availability $availability, array $data): Availability
    {
        return $this->availabilities->update($availability, $data);
    }

    // apaga uma disponibilidade
    public function delete(Availability $availability): bool
    {
        return $this->availabilities->delete($availability);
    }
}
