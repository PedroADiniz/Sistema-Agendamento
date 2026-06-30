<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Cria a tabela das disponibilidades (janelas de horário dos atendentes)
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('availabilities', function (Blueprint $table) {
            $table->id();

            // atendente dono da janela (se apagar o atendente, apaga as janelas)
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            // dia da semana: 0 = domingo ... 6 = sábado
            $table->unsignedTinyInteger('weekday');

            $table->time('start_time');                // hora inicial
            $table->time('end_time');                  // hora final
            $table->boolean('active')->default(true);  // se a janela está ativa

            $table->timestamps();

            // índice para acelerar a busca por atendente/dia/ativo
            $table->index(['user_id', 'weekday', 'active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('availabilities');
    }
};
