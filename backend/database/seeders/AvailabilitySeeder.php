<?php

namespace Database\Seeders;

use App\Models\Availability;
use App\Models\User;
use Illuminate\Database\Seeder;

// Cria as janelas de disponibilidade dos atendentes (seg a sex, manhã e tarde)
class AvailabilitySeeder extends Seeder
{
    public function run(): void
    {
        // pega todos os atendentes
        $atendentes = User::where('role', 'atendente')->get();

        // para cada atendente...
        foreach ($atendentes as $atendente) {
            // ...e para cada dia de segunda (1) a sexta (5)
            for ($weekday = 1; $weekday <= 5; $weekday++) {
                // janela da manhã: 08:00 às 12:00
                Availability::updateOrCreate(
                    [
                        'user_id' => $atendente->id,
                        'weekday' => $weekday,
                        'start_time' => '08:00:00',
                    ],
                    [
                        'end_time' => '12:00:00',
                        'active' => true,
                    ]
                );

                // janela da tarde: 13:00 às 17:00
                Availability::updateOrCreate(
                    [
                        'user_id' => $atendente->id,
                        'weekday' => $weekday,
                        'start_time' => '13:00:00',
                    ],
                    [
                        'end_time' => '17:00:00',
                        'active' => true,
                    ]
                );
            }
        }
    }
}
