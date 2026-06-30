<?php

use Illuminate\Support\Facades\Artisan;

// Comando de exemplo do Laravel (sem uso de negócio).
Artisan::command('inspire', function () {
    $this->comment('Sistema de Agendamentos — API rodando.');
})->purpose('Mensagem de status');
