<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

// Chama os seeders na ordem certa: usuários -> disponibilidades -> agendamentos
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            AvailabilitySeeder::class,
            AppointmentSeeder::class,
        ]);
    }
}
