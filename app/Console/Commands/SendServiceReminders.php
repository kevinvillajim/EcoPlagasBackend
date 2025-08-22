<?php

namespace App\Console\Commands;

use App\Jobs\SendServiceReminderJob;
use App\Models\Service;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SendServiceReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'services:send-reminders 
                            {--type=all : Type of reminders to send (all, three-days, same-day)}
                            {--dry-run : Show what would be sent without actually sending}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send automated service reminders to clients';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $type = $this->option('type');
        $dryRun = $this->option('dry-run');
        
        $this->info('🔄 Iniciando envío de recordatorios de servicio...');
        
        $totalSent = 0;
        
        // Recordatorios 3 días antes
        if ($type === 'all' || $type === 'three-days') {
            $totalSent += $this->sendThreeDayReminders($dryRun);
        }
        
        // Recordatorios del mismo día
        if ($type === 'all' || $type === 'same-day') {
            $totalSent += $this->sendSameDayReminders($dryRun);
        }
        
        if ($dryRun) {
            $this->info("✅ Dry run completado. Se habrían enviado {$totalSent} recordatorios.");
        } else {
            $this->info("✅ Proceso completado. Enviados {$totalSent} recordatorios.");
        }
        
        return 0;
    }

    /**
     * Send 3-day advance reminders
     */
    private function sendThreeDayReminders($dryRun = false): int
    {
        $threeDaysFromNow = Carbon::now()->addDays(3)->startOfDay();
        $endOfDay = $threeDaysFromNow->copy()->endOfDay();
        
        $services = Service::with('user')
            ->where('status', Service::STATUS_SCHEDULED)
            ->whereBetween('scheduled_date', [$threeDaysFromNow, $endOfDay])
            ->whereHas('user', function ($query) {
                $query->whereNotNull('email');
            })
            ->get();

        $this->info("📅 Servicios programados para {$threeDaysFromNow->format('d/m/Y')}: {$services->count()}");
        
        $sent = 0;
        foreach ($services as $service) {
            if ($this->shouldSendReminder($service, 'three_days')) {
                if ($dryRun) {
                    $this->line("  [DRY RUN] Recordatorio 3 días: {$service->user->name} ({$service->user->email}) - {$service->type}");
                } else {
                    SendServiceReminderJob::dispatch($service, 'three_days');
                    $this->line("  ✉️  Enviado a: {$service->user->name} ({$service->user->email})");
                }
                $sent++;
            }
        }
        
        if ($sent > 0) {
            $this->info("✅ Recordatorios de 3 días enviados: {$sent}");
        }
        
        return $sent;
    }

    /**
     * Send same-day reminders
     */
    private function sendSameDayReminders($dryRun = false): int
    {
        $today = Carbon::now()->startOfDay();
        $endOfDay = Carbon::now()->endOfDay();
        
        $services = Service::with('user')
            ->where('status', Service::STATUS_SCHEDULED)
            ->whereBetween('scheduled_date', [$today, $endOfDay])
            ->whereHas('user', function ($query) {
                $query->whereNotNull('email');
            })
            ->get();

        $this->info("📅 Servicios programados para hoy: {$services->count()}");
        
        $sent = 0;
        foreach ($services as $service) {
            if ($this->shouldSendReminder($service, 'same_day')) {
                if ($dryRun) {
                    $this->line("  [DRY RUN] Recordatorio hoy: {$service->user->name} ({$service->user->email}) - {$service->type}");
                } else {
                    SendServiceReminderJob::dispatch($service, 'same_day');
                    $this->line("  ✉️  Enviado a: {$service->user->name} ({$service->user->email})");
                }
                $sent++;
            }
        }
        
        if ($sent > 0) {
            $this->info("✅ Recordatorios del día enviados: {$sent}");
        }
        
        return $sent;
    }

    /**
     * Check if reminder should be sent (avoid duplicates)
     */
    private function shouldSendReminder(Service $service, string $type): bool
    {
        // Verificar si ya se envió una notificación similar hoy
        $today = Carbon::now()->startOfDay();
        
        $existingNotification = $service->user->notifications()
            ->where('type', 'service_reminder')
            ->where('created_at', '>=', $today)
            ->whereJsonContains('data->service_id', $service->id)
            ->whereJsonContains('data->reminder_type', $type)
            ->exists();
            
        return !$existingNotification;
    }
}