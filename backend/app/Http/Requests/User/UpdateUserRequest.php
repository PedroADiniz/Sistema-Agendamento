<?php

namespace App\Http\Requests\User;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

// Validação ao EDITAR um usuário.
// E-mail e senha não entram aqui porque não podem ser alterados.
class UpdateUserRequest extends FormRequest
{
    // a permissão é checada na Policy, dentro do controller
    public function authorize(): bool
    {
        return true;
    }

    // regras dos campos editáveis
    public function rules(): array
    {
        return [
            'name' => ['nullable', 'string', 'max:255'],            // nome opcional
            'role' => ['nullable', Rule::in(UserRole::values())],   // tipo válido
        ];
    }

    // mensagem de erro amigável
    public function messages(): array
    {
        return [
            'role.in' => 'Tipo de usuário inválido.',
        ];
    }
}
