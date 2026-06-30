<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

// Cria alguns agendamentos de exemplo, para a consulta de horários
// já mostrar slots ocupados (que somem da lista de livres).
class AppointmentSeeder extends Seeder
{
    public function run(): void
    {
        // pega a atendente Ana
        $ana = User::where('email', 'ana@agendamento.com')->first();

        // se não existir, não faz nada
        if (! $ana) {
            return;
        }

        // pega a próxima segunda-feira (dia em que a Ana atende)
        $nextMonday = Carbon::today()->next(Carbon::MONDAY)->format('Y-m-d');

        // ocupa dois horários da manhã: 08:00 e 10:00
        foreach (['08:00:00' => '09:00:00', '10:00:00' => '11:00:00'] as $start => $end) {
            Appointment::updateOrCreate(
                [
                    'user_id' => $ana->id,
                    'scheduled_date' => $nextMonday,
                    'start_time' => $start,
                ],
                [
                    'client_name' => 'Cliente Exemplo',
                    'client_email' => 'cliente@exemplo.com',
                    'end_time' => $end,
                    'status' => 'scheduled',
                ]
            );
        }
    }
}
