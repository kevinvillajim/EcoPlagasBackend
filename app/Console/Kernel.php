<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Enviar recordatorios de 3 días todos los días a las 9:00 AM
        $schedule->command('services:send-reminders --type=three-days')
                 ->dailyAt('09:00')
                 ->withoutOverlapping()
                 ->runInBackground();

        // Enviar recordatorios del mismo día todos los días a las 8:00 AM
        $schedule->command('services:send-reminders --type=same-day')
                 ->dailyAt('08:00')
                 ->withoutOverlapping()
                 ->runInBackground();

        // Verificar certificados próximos a vencer (7 días antes) todos los días a las 10:00 AM
        $schedule->command('certificates:check-expiring --days=7')
                 ->dailyAt('10:00')
                 ->withoutOverlapping()
                 ->runInBackground();

        // Procesar trabajos en cola cada minuto (importante para emails)
        $schedule->command('queue:work --stop-when-empty')
                 ->everyMinute()
                 ->withoutOverlapping()
                 ->runInBackground();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}