<?php

namespace App\Http\Requests\Schedule;

use Illuminate\Foundation\Http\FormRequest;

// Validação da consulta de horários: vem pela URL (?attendant_id=..&date=..)
class AvailableSlotsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    // regras dos campos
    public function rules(): array
    {
        return [
            'attendant_id' => ['required', 'integer', 'exists:users,id'], // atendente tem que existir
            'date' => ['required', 'date_format:Y-m-d'],                  // data no formato ano-mes-dia
        ];
    }

    // mensagens de erro amigáveis
    public function messages(): array
    {
        return [
            'attendant_id.required' => 'Selecione o atendente.',
            'attendant_id.exists' => 'Atendente inválido.',
            'date.required' => 'Selecione a data.',
            'date.date_format' => 'A data deve estar no formato AAAA-MM-DD.',
        ];
    }
}
