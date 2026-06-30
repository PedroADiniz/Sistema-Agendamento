<?php

namespace App\Http\Requests\User;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

// Validação ao CRIAR um usuário
class StoreUserRequest extends FormRequest
{
    // a permissão (só admin) é checada na Policy, dentro do controller
    public function authorize(): bool
    {
        return true;
    }

    // regras dos campos
    public function rules(): array
    {
        return [
            // nome não é obrigatório
            'name' => ['nullable', 'string', 'max:255'],
            // tipo precisa ser admin ou atendente
            'role' => ['nullable', Rule::in(UserRole::values())],
            // e-mail obrigatório, válido e que ainda não exista no banco
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            // senha obrigatória, mínimo 8 e tem que bater com a confirmação
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            // campo da confirmação da senha
            'password_confirmation' => ['required', 'string'],
        ];
    }

    // mensagens de erro amigáveis
    public function messages(): array
    {
        return [
            'email.required' => 'O e-mail é obrigatório.',
            'email.email' => 'Informe um e-mail válido.',
            'email.unique' => 'Este e-mail já está cadastrado.',
            'password.required' => 'A senha é obrigatória.',
            'password.min' => 'A senha deve ter no mínimo 8 caracteres.',
            'password.confirmed' => 'A confirmação de senha não confere.',
            'password_confirmation.required' => 'Confirme a senha.',
            'role.in' => 'Tipo de usuário inválido.',
        ];
    }

    // nomes dos campos para as mensagens padrão
    public function attributes(): array
    {
        return [
            'email' => 'e-mail',
            'password' => 'senha',
            'password_confirmation' => 'confirmação de senha',
            'name' => 'nome',
            'role' => 'tipo de usuário',
        ];
    }
}
