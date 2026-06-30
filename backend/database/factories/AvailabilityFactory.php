<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

// Gera disponibilidades falsas para testes/seed
class AvailabilityFactory extends Factory
{
    // valores padrão de uma disponibilidade gerada
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->atendente(), // cria um atendente junto
            'weekday' => fake()->numberBetween(1, 5),  // um dia de seg a sex
            'start_time' => '08:00',
            'end_time' => '12:00',
            'active' => true,
        ];
    }
}
