<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Registre os comandos Artisan customizados da aplicação.
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        // Você também pode registrar comandos diretamente aqui:
        // require base_path('routes/console.php');
    }

    /**
     * Defina o agendamento de tarefas do Artisan.
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('products:import')->dailyAt('03:00');
        // Também pode usar ->everyMinute() para testar mais rápido
    }
}
