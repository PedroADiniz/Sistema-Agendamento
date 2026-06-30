<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Model da disponibilidade: a janela de horário de um atendente num dia da semana
class Availability extends Model
{
    use HasFactory;

    // campos liberados para preenchimento em massa
    protected $fillable = [
        'user_id',
        'weekday',
        'start_time',
        'end_time',
        'active',
    ];

    // conversões de tipo
    protected function casts(): array
    {
        return [
            'weekday' => 'integer', // dia da semana como número
            'active' => 'boolean',  // ativo como true/false
        ];
    }

    // a disponibilidade pertence a um atendente
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
