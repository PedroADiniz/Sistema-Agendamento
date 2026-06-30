<?php

namespace Database\Factories;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

// Gera usuários falsos para testes/seed
class UserFactory extends Factory
{
    // valores padrão de um usuário gerado
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'role' => UserRole::ATENDENTE->value, // por padrão, atendente
            'password' => Hash::make('12345678'),
        ];
    }

    // variação: gerar como admin
    public function admin(): static
    {
        return $this->state(fn () => ['role' => UserRole::ADMIN->value]);
    }

    // variação: gerar como atendente
    public function atendente(): static
    {
        return $this->state(fn () => ['role' => UserRole::ATENDENTE->value]);
    }
}
