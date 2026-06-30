<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

// Monta o JSON de saída de um agendamento
class AppointmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'client_name' => $this->client_name,
            'client_email' => $this->client_email,
            'scheduled_date' => $this->scheduled_date?->format('Y-m-d'),
            'start_time' => substr((string) $this->start_time, 0, 5), // só HH:MM
            'end_time' => substr((string) $this->end_time, 0, 5),
            'status' => $this->status,
        ];
    }
}
