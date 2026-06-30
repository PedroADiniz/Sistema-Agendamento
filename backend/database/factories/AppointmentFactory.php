<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

// Gera agendamentos falsos para testes/seed
class AppointmentFactory extends Factory
{
    // valores padrão de um agendamento gerado
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->atendente(), // cria um atendente junto
            'client_name' => fake()->name(),
            'client_email' => fake()->safeEmail(),
            'scheduled_date' => now()->format('Y-m-d'),
            'start_time' => '08:00',
            'end_time' => '09:00',
            'status' => 'scheduled',
        ];
    }
}
