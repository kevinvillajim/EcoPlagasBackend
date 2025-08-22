<?php

namespace App\Jobs;

use App\Mail\ServiceReminderMail;
use App\Models\Service;
use App\Services\EmailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendServiceReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $service;
    public $reminderType; // 'three_days' or 'same_day'

    /**
     * Create a new job instance.
     */
    public function __construct(Service $service, string $reminderType)
    {
        $this->service = $service;
        $this->reminderType = $reminderType;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        try {
            $user = $this->service->user;
            
            if (!$user || !$user->email) {
                Log::warning('Service reminder not sent - no user or email', [
                    'service_id' => $this->service->id,
                    'user_id' => $user?->id
                ]);
                return;
            }

            // Verificar si el email está habilitado
            $emailService = app(EmailService::class);
            $emailStatus = $emailService->getEmailStatus();
            
            if ($emailStatus['enabled']) {
                Mail::to($user->email)->send(new ServiceReminderMail($user, $this->service, $this->reminderType));
                
                Log::info('Service reminder email sent', [
                    'service_id' => $this->service->id,
                    'user_email' => $user->email,
                    'reminder_type' => $this->reminderType,
                    'scheduled_date' => $this->service->scheduled_date
                ]);
            } else {
                // Log reminder for development/testing
                $this->logServiceReminder($user, $this->service, $this->reminderType);
            }

            // Crear notificación interna también
            $this->createInternalNotification($user);
            
        } catch (\Exception $e) {
            Log::error('Error sending service reminder', [
                'service_id' => $this->service->id,
                'reminder_type' => $this->reminderType,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Re-lanzar excepción para que Laravel pueda reintentar si es necesario
            throw $e;
        }
    }

    /**
     * Create internal notification
     */
    private function createInternalNotification($user)
    {
        $message = $this->reminderType === 'three_days' 
            ? "Recordatorio: Tienes un servicio programado para el {$this->service->scheduled_date->format('d/m/Y')} a las {$this->service->scheduled_date->format('H:i')}"
            : "Hoy tienes programado un servicio de {$this->service->type} a las {$this->service->scheduled_date->format('H:i')}";

        $user->notifications()->create([
            'type' => 'service_reminder',
            'title' => $this->reminderType === 'three_days' ? 'Servicio en 3 días' : 'Servicio hoy',
            'message' => $message,
            'data' => [
                'service_id' => $this->service->id,
                'reminder_type' => $this->reminderType,
                'scheduled_date' => $this->service->scheduled_date,
                'service_type' => $this->service->type
            ],
            'read_at' => null,
        ]);
    }

    /**
     * Log service reminder for development/testing
     */
    private function logServiceReminder($user, $service, $reminderType)
    {
        $subject = $reminderType === 'three_days' 
            ? 'Recordatorio: Servicio programado para el ' . $service->scheduled_date->format('d/m/Y')
            : 'Hoy: Servicio de control de plagas programado';

        Log::info('===== SERVICE REMINDER EMAIL =====');
        Log::info("Para: {$user->email} ({$user->name})");
        Log::info("Asunto: {$subject}");
        Log::info("Tipo: {$service->type}");
        Log::info("Fecha: {$service->scheduled_date->format('d/m/Y H:i')}");
        Log::info("Dirección: {$service->address}");
        Log::info("Tipo de recordatorio: {$reminderType}");
        Log::info("===================================");
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception)
    {
        Log::error('Service reminder job failed', [
            'service_id' => $this->service->id,
            'reminder_type' => $this->reminderType,
            'error' => $exception->getMessage()
        ]);
    }
}