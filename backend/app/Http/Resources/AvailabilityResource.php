<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

// Monta o JSON de saída de uma disponibilidade
class AvailabilityResource extends JsonResource
{
    // nomes dos dias da semana (0 = domingo ... 6 = sábado)
    private const WEEKDAYS = [
        0 => 'Domingo',
        1 => 'Segunda-feira',
        2 => 'Terça-feira',
        3 => 'Quarta-feira',
        4 => 'Quinta-feira',
        5 => 'Sexta-feira',
        6 => 'Sábado',
    ];

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            // mostra o nome do atendente só quando o relacionamento foi carregado
            'attendant' => $this->whenLoaded('user', fn () => [
                'id' => $this->user->id,
                'name' => $this->user->name,
            ]),
            'weekday' => $this->weekday,
            'weekday_label' => self::WEEKDAYS[$this->weekday] ?? '—', // nome do dia
            'start_time' => substr((string) $this->start_time, 0, 5), // só HH:MM
            'end_time' => substr((string) $this->end_time, 0, 5),
            'active' => (bool) $this->active,
        ];
    }
}
