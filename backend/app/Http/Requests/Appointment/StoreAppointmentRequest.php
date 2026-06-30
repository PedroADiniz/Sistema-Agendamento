<?php

namespace App\Http\Requests\Appointment;

use Illuminate\Foundation\Http\FormRequest;

// Validação ao criar um agendamento (reservar um horário)
class StoreAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    // regras dos campos
    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id'], // atendente
            'scheduled_date' => ['required', 'date_format:Y-m-d'],   // data
            'start_time' => ['required', 'date_format:H:i'],         // hora escolhida
            'client_name' => ['required', 'string', 'max:255'],      // nome do cliente
            'client_email' => ['nullable', 'email', 'max:255'],      // e-mail do cliente (opcional)
        ];
    }

    // mensagens de erro amigáveis
    public function messages(): array
    {
        return [
            'user_id.required' => 'Selecione o atendente.',
            'user_id.exists' => 'Atendente inválido.',
            'scheduled_date.required' => 'Selecione a data.',
            'start_time.required' => 'Selecione o horário.',
            'client_name.required' => 'Informe o nome do cliente.',
        ];
    }
}
