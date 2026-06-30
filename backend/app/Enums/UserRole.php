<?php

namespace App\Enums;

// Os dois tipos de usuário possíveis no sistema
enum UserRole: string
{
    case ADMIN = 'admin';          // pode tudo
    case ATENDENTE = 'atendente';  // só vê a lista e edita o próprio cadastro

    // devolve o nome bonito para mostrar na tela
    public function label(): string
    {
        // escolhe o texto de acordo com o tipo
        return match ($this) {
            self::ADMIN => 'Administrador',
            self::ATENDENTE => 'Atendente',
        };
    }

    // devolve os valores em array (usado na validação "in:admin,atendente")
    public static function values(): array
    {
        // pega cada caso do enum e retorna só o valor (string)
        return array_map(fn (self $c) => $c->value, self::cases());
    }
}
