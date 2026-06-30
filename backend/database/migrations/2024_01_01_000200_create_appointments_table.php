<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Cria a tabela dos agendamentos (cada linha é um horário ocupado)
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();

            // atendente que foi agendado (se apagar o atendente, apaga os agendamentos)
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            // dados do cliente (são de exemplo, vêm do seeder)
            $table->string('client_name');
            $table->string('client_email')->nullable();

            $table->date('scheduled_date'); // data do agendamento
            $table->time('start_time');     // hora de início
            $table->time('end_time');       // hora de fim

            // situação: agendado ou cancelado
            $table->enum('status', ['scheduled', 'cancelled'])->default('scheduled');

            $table->timestamps();

            // não deixa marcar dois agendamentos no mesmo atendente/data/horário
            $table->unique(['user_id', 'scheduled_date', 'start_time'], 'uniq_slot');
            $table->index(['user_id', 'scheduled_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
