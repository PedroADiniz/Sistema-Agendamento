<?php

namespace App\Repositories\Eloquent;

use App\Models\Appointment;
use App\Repositories\Contracts\AppointmentRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

// Implementação do repositório de agendamentos usando Eloquent
class AppointmentRepository implements AppointmentRepositoryInterface
{
    // pega os agendamentos marcados do atendente naquela data
    public function scheduledForUserAndDate(int $userId, string $date): Collection
    {
        return Appointment::where('user_id', $userId)
            ->whereDate('scheduled_date', $date)
            ->where('status', 'scheduled')
            ->orderBy('start_time')
            ->get();
    }

    // pega todos os agendamentos de um atendente
    public function forUser(int $userId): Collection
    {
        return Appointment::where('user_id', $userId)
            ->orderBy('scheduled_date')
            ->orderBy('start_time')
            ->get();
    }

    // salva um agendamento novo
    public function create(array $data): Appointment
    {
        return Appointment::create($data);
    }
}
