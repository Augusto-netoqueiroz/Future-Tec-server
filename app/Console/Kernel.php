<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Defina os comandos do Artisan.
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }

    /**
     * Defina a programação das tarefas do sistema.
     */
    protected function schedule(Schedule $schedule)
    {
        // Aqui você agenda os comandos personalizados
        $schedule->command('sessions:check-expired')->everyMinute();
        $schedule->command('send:daily-ticket-summary')->dailyAt('08:00');
        $schedule->command('glpi:get-discord')->everyTwoMinutes();
        $schedule->command('glpi:process-discord')->everyFiveMinutes();
    }
}
