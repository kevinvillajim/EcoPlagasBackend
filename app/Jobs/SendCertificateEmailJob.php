<?php

namespace App\Jobs;

use App\Mail\CertificateMail;
use App\Models\Certificate;
use App\Services\EmailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendCertificateEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $certificate;

    /**
     * Create a new job instance.
     */
    public function __construct(Certificate $certificate)
    {
        $this->certificate = $certificate;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        try {
            $user = $this->certificate->user;
            
            if (!$user || !$user->email) {
                Log::warning('Certificate email not sent - no user or email', [
                    'certificate_id' => $this->certificate->id,
                    'user_id' => $user?->id
                ]);
                return;
            }

            // Verificar si el email está habilitado
            $emailService = app(EmailService::class);
            $emailStatus = $emailService->getEmailStatus();
            
            if ($emailStatus['enabled']) {
                Mail::to($user->email)->send(new CertificateMail($user, $this->certificate));
                
                Log::info('Certificate email sent', [
                    'certificate_id' => $this->certificate->id,
                    'certificate_number' => $this->certificate->certificate_number,
                    'user_email' => $user->email,
                    'service_id' => $this->certificate->service_id
                ]);
            } else {
                // Log certificate for development/testing
                $this->logCertificateEmail($user, $this->certificate);
            }

            // Crear notificación interna también
            $this->createInternalNotification($user);
            
        } catch (\Exception $e) {
            Log::error('Error sending certificate email', [
                'certificate_id' => $this->certificate->id,
                'certificate_number' => $this->certificate->certificate_number,
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
        $message = "Tu certificado de control de plagas {$this->certificate->certificate_number} ha sido generado y enviado por correo electrónico. Es válido hasta el {$this->certificate->valid_until->format('d/m/Y')}.";

        $user->notifications()->create([
            'type' => 'certificate_generated',
            'title' => 'Certificado Generado',
            'message' => $message,
            'data' => [
                'certificate_id' => $this->certificate->id,
                'certificate_number' => $this->certificate->certificate_number,
                'valid_until' => $this->certificate->valid_until,
                'service_id' => $this->certificate->service_id
            ],
            'read_at' => null,
        ]);
    }

    /**
     * Log certificate email for development/testing
     */
    private function logCertificateEmail($user, $certificate)
    {
        Log::info('===== CERTIFICATE EMAIL =====');
        Log::info("Para: {$user->email} ({$user->name})");
        Log::info("Asunto: Certificado de Control de Plagas - {$certificate->certificate_number}");
        Log::info("Certificado: {$certificate->certificate_number}");
        Log::info("Tipo: {$certificate->type}");
        Log::info("Válido hasta: {$certificate->valid_until->format('d/m/Y')}");
        Log::info("Servicio ID: {$certificate->service_id}");
        Log::info("==============================");
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception)
    {
        Log::error('Certificate email job failed', [
            'certificate_id' => $this->certificate->id,
            'certificate_number' => $this->certificate->certificate_number,
            'error' => $exception->getMessage()
        ]);
    }
}