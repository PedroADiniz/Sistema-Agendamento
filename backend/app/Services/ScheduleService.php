<?php

namespace App\Services;

use App\Models\Appointment;
use App\Repositories\Contracts\AvailabilityRepositoryInterface;
use App\Repositories\Contracts\AppointmentRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

// Regra de negócio dos horários: monta os slots livres e cria agendamentos.
// Decisão do projeto: cada slot tem 60 minutos.
class ScheduleService
{
    // duração de cada horário, em minutos
    public const SLOT_MINUTES = 60;

    // recebe os dois repositórios por injeção de dependência
    public function __construct(
        private readonly AvailabilityRepositoryInterface $availabilities,
        private readonly AppointmentRepositoryInterface $appointments,
    ) {}

    // devolve os horários livres de um atendente numa data
    public function availableSlots(int $attendantId, string $date): array
    {
        // transforma a data em objeto e descobre o dia da semana (0=domingo ... 6=sábado)
        $day = Carbon::createFromFormat('Y-m-d', $date)->startOfDay();
        $weekday = $day->dayOfWeek;

        // pega as janelas ativas do atendente nesse dia da semana
        $windows = $this->availabilities->activeForUserAndWeekday($attendantId, $weekday);

        // se não tem janela, não tem horário livre
        if ($windows->isEmpty()) {
            return [];
        }

        // pega os horários já ocupados naquela data (só interessa a hora de início)
        $occupied = $this->appointments
            ->scheduledForUserAndDate($attendantId, $date)
            ->map(fn ($a) => $this->normalizeTime($a->start_time))
            ->all();

        $slots = [];

        // para cada janela, fatia em pedaços de 60 min
        foreach ($windows as $window) {
            // ponteiro começa no início da janela
            $cursor = $this->timeOn($day, $window->start_time);
            $end = $this->timeOn($day, $window->end_time);

            // enquanto couber um slot inteiro de 60 min dentro da janela
            while ($cursor->copy()->addMinutes(self::SLOT_MINUTES)->lte($end)) {
                $slotStart = $cursor->format('H:i');
                $slotEnd = $cursor->copy()->addMinutes(self::SLOT_MINUTES)->format('H:i');

                // só adiciona se esse horário não estiver ocupado
                if (! in_array($slotStart, $occupied, true)) {
                    $slots[$slotStart] = ['start' => $slotStart, 'end' => $slotEnd];
                }

                // avança o ponteiro para o próximo slot
                $cursor->addMinutes(self::SLOT_MINUTES);
            }
        }

        // ordena pela hora e reindexa o array
        ksort($slots);

        return array_values($slots);
    }

    // devolve todos os slots do dia (livres e ocupados) com dados do cliente nos ocupados
    public function allSlots(int $attendantId, string $date): array
    {
        $day     = Carbon::createFromFormat('Y-m-d', $date)->startOfDay();
        $weekday = $day->dayOfWeek;

        $windows = $this->availabilities->activeForUserAndWeekday($attendantId, $weekday);

        if ($windows->isEmpty()) {
            return [];
        }

        // indexa os agendamentos pela hora de início para busca rápida
        $booked = $this->appointments
            ->scheduledForUserAndDate($attendantId, $date)
            ->keyBy(fn ($a) => $this->normalizeTime($a->start_time));

        $slots = [];

        foreach ($windows as $window) {
            $cursor = $this->timeOn($day, $window->start_time);
            $end    = $this->timeOn($day, $window->end_time);

            while ($cursor->copy()->addMinutes(self::SLOT_MINUTES)->lte($end)) {
                $slotStart   = $cursor->format('H:i');
                $slotEnd     = $cursor->copy()->addMinutes(self::SLOT_MINUTES)->format('H:i');
                $appointment = $booked->get($slotStart);

                // slot livre ou ocupado, sempre retorna com status e dados do cliente
                $slots[$slotStart] = [
                    'start'        => $slotStart,
                    'end'          => $slotEnd,
                    'status'       => $appointment ? 'booked' : 'available',
                    'client_name'  => $appointment?->client_name,
                    'client_email' => $appointment?->client_email,
                ];

                $cursor->addMinutes(self::SLOT_MINUTES);
            }
        }

        ksort($slots);
        return array_values($slots);
    }

    // cria um agendamento, conferindo antes se o horário está mesmo livre
    public function book(array $data): Appointment
    {
        // normaliza a hora pedida (HH:MM)
        $start = $this->normalizeTime($data['start_time']);

        // pega os horários livres do atendente na data
        $available = collect($this->availableSlots($data['user_id'], $data['scheduled_date']));
        // procura o slot pedido entre os livres
        $slot = $available->firstWhere('start', $start);

        // se o horário não está livre, devolve erro de validação (422)
        if ($slot === null) {
            throw ValidationException::withMessages([
                'start_time' => ['Este horário não está disponível para agendamento.'],
            ]);
        }

        // salva o agendamento ocupando o slot
        return $this->appointments->create([
            'user_id' => $data['user_id'],
            'client_name' => $data['client_name'],
            'client_email' => $data['client_email'] ?? null,
            'scheduled_date' => $data['scheduled_date'],
            'start_time' => $slot['start'],
            'end_time' => $slot['end'],
            'status' => 'scheduled',
        ]);
    }

    // junta uma data com uma hora "HH:MM" e devolve um Carbon
    private function timeOn(Carbon $day, string $time): Carbon
    {
        [$h, $m] = array_pad(explode(':', $time), 2, '00');

        return $day->copy()->setTime((int) $h, (int) $m, 0);
    }

    // deixa a hora no formato "HH:MM" (tira os segundos) para comparar
    private function normalizeTime(string $time): string
    {
        [$h, $m] = array_pad(explode(':', $time), 2, '00');

        return sprintf('%02d:%02d', (int) $h, (int) $m);
    }
}
