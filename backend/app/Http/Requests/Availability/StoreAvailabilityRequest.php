<?php

namespace App\Http\Requests\Availability;

use Illuminate\Foundation\Http\FormRequest;

// Validação ao CRIAR uma disponibilidade.
// Regra importante: a hora final tem que ser maior que a inicial.
class StoreAvailabilityRequest extends FormRequest
{
    // só admin (checado na Policy, no controller)
    public function authorize(): bool
    {
        return true;
    }

    // regras dos campos
    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id'], // atendente tem que existir
            'weekday' => ['required', 'integer', 'between:0,6'],      // dia da semana de 0 a 6
            'start_time' => ['required', 'date_format:H:i'],          // hora inicial no formato HH:MM
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'], // final > inicial
            'active' => ['required', 'boolean'],                      // ativo true/false
        ];
    }

    // mensagens de erro amigáveis
    public function messages(): array
    {
        return [
            'user_id.required' => 'Selecione o atendente.',
            'user_id.exists' => 'Atendente inválido.',
            'weekday.required' => 'Selecione o dia da semana.',
            'weekday.between' => 'Dia da semana inválido.',
            'start_time.required' => 'Informe a hora inicial.',
            'start_time.date_format' => 'A hora inicial deve estar no formato HH:MM.',
            'end_time.required' => 'Informe a hora final.',
            'end_time.date_format' => 'A hora final deve estar no formato HH:MM.',
            'end_time.after' => 'A hora final deve ser maior que a hora inicial.',
            'active.required' => 'Informe se a disponibilidade está ativa.',
        ];
    }
}
