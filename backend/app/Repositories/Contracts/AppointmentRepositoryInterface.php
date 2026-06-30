<?php

namespace App\Repositories\Contracts;

use App\Models\Appointment;
use Illuminate\Database\Eloquent\Collection;

// Contrato do repositório de agendamentos
interface AppointmentRepositoryInterface
{
    // agendamentos marcados de um atendente numa data (para saber os horários ocupados)
    public function scheduledForUserAndDate(int $userId, string $date): Collection;

    // agendamentos de um atendente
    public function forUser(int $userId): Collection;

    // cria um agendamento
    public function create(array $data): Appointment;
}
