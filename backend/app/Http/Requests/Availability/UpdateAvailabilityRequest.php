<?php

namespace App\Http\Requests\Availability;

use Illuminate\Foundation\Http\FormRequest;

// Validação ao EDITAR uma disponibilidade (não troca o atendente).
class UpdateAvailabilityRequest extends FormRequest
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
            'weekday' => ['required', 'integer', 'between:0,6'],      // dia da semana
            'start_time' => ['required', 'date_format:H:i'],          // hora inicial
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'], // final > inicial
            'active' => ['required', 'boolean'],                      // ativo true/false
        ];
    }

    // mensagens de erro amigáveis
    public function messages(): array
    {
        return [
            'end_time.after' => 'A hora final deve ser maior que a hora inicial.',
            'start_time.date_format' => 'A hora inicial deve estar no formato HH:MM.',
            'end_time.date_format' => 'A hora final deve estar no formato HH:MM.',
        ];
    }
}
