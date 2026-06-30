<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Model do agendamento (um horário ocupado de um atendente)
class Appointment extends Model
{
    use HasFactory;

    // campos liberados para preenchimento em massa
    protected $fillable = [
        'user_id',
        'client_name',
        'client_email',
        'scheduled_date',
        'start_time',
        'end_time',
        'status',
    ];

    // conversões de tipo
    protected function casts(): array
    {
        return [
            'scheduled_date' => 'date:Y-m-d', // data sempre no formato ano-mes-dia
        ];
    }

    // o agendamento pertence a um atendente
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
