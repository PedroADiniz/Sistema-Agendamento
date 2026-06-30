<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

// Validação dos dados do login
class LoginRequest extends FormRequest
{
    // libera qualquer um (rota pública)
    public function authorize(): bool
    {
        return true;
    }

    // regras dos campos
    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],     // e-mail obrigatório e válido
            'password' => ['required', 'string'],  // senha obrigatória
        ];
    }

    // mensagens de erro amigáveis
    public function messages(): array
    {
        return [
            'email.required' => 'O e-mail é obrigatório.',
            'email.email' => 'Informe um e-mail válido.',
            'password.required' => 'A senha é obrigatória.',
        ];
    }
}
