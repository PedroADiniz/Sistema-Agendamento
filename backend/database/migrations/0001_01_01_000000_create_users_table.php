<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Cria a tabela de usuários (admin e atendente)
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();           // Nome (não obrigatório no formulário)
            $table->string('email')->unique();             // E-mail* — único na base
            $table->enum('role', ['admin', 'atendente'])   // Tipo de usuário
                  ->default('atendente');
            $table->string('password');                    // Senha* (hash)
            $table->timestamps();
            $table->softDeletes();                         // Soft delete (decisão documentada no README)
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
