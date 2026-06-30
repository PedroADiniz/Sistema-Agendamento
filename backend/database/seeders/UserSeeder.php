<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

// Cria o admin e alguns atendentes. Todos com a senha "12345678".
class UserSeeder extends Seeder
{
    public function run(): void
    {
        // cria (ou atualiza) o administrador padrão
        User::updateOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Administrador',
                'role' => UserRole::ADMIN->value,
                'password' => Hash::make('12345678'),
            ]
        );

        // lista de atendentes de exemplo
        $atendentes = [
            ['name' => 'Ana Souza', 'email' => 'ana@agendamento.com'],
            ['name' => 'Bruno Lima', 'email' => 'bruno@agendamento.com'],
            ['name' => 'Carla Dias', 'email' => 'carla@agendamento.com'],
        ];

        // cria cada atendente da lista
        foreach ($atendentes as $a) {
            User::updateOrCreate(
                ['email' => $a['email']],
                [
                    'name' => $a['name'],
                    'role' => UserRole::ATENDENTE->value,
                    'password' => Hash::make('12345678'),
                ]
            );
        }
    }
}
