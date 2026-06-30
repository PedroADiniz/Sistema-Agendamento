<?php

namespace App\Http\Controllers;

use App\Http\Requests\Appointment\StoreAppointmentRequest;
use App\Http\Requests\Schedule\AvailableSlotsRequest;
use App\Http\Resources\AppointmentResource;
use App\Services\ScheduleService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

// Controller dos horários: consulta os livres e cria agendamento.
class ScheduleController extends Controller
{
    // recebe o service por injeção de dependência
    public function __construct(
        private readonly ScheduleService $schedule
    ) {}

    // GET /api/schedule/available?attendant_id=..&date=.. -> lista os horários livres
    public function available(AvailableSlotsRequest $request): JsonResponse
    {
        // dados já validados (atendente + data)
        $data = $request->validated();

        // pede ao service a lista de slots livres
        $slots = $this->schedule->availableSlots(
            (int) $data['attendant_id'],
            $data['date']
        );

        return ApiResponse::success([
            'attendant_id' => (int) $data['attendant_id'],
            'date' => $data['date'],
            'slot_minutes' => ScheduleService::SLOT_MINUTES, // tamanho do slot (60)
            'slots' => $slots,
        ], 'Horários disponíveis listados com sucesso.');
    }

    // POST /api/appointments -> cria um agendamento ocupando um horário livre
    public function store(StoreAppointmentRequest $request): JsonResponse
    {
        // o service confere se o horário está livre e salva
        $appointment = $this->schedule->book($request->validated());

        return ApiResponse::success(
            new AppointmentResource($appointment),
            'Agendamento criado com sucesso.',
            201
        );
    }
}
