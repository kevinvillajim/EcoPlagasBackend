<?php

namespace App\Jobs;

use App\Models\Certificate;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendCertificateExpiryNotificationJob // Removed ShouldQueue to run synchronously
{
    use Queueable, InteractsWithQueue, SerializesModels;

    protected $certificate;

    /**
     * Create a new job instance.
     */
    public function __construct(Certificate $certificate)
    {
        $this->certificate = $certificate;
        $this->onQueue('emails');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $certificate = $this->certificate->load(['user', 'service']);
            
            if (!$certificate->user || !$certificate->user->email) {
                Log::warning('Certificate expiry notification skipped - no user email', [
                    'certificate_id' => $certificate->id
                ]);
                return;
            }

            // Calculate days until expiry
            $daysUntilExpiry = (int) round(now()->diffInDays($certificate->valid_until, false));
            
            // Send email to client
            Mail::send('emails.certificate-expiry', [
                'certificate' => $certificate,
                'client' => $certificate->user,
                'service' => $certificate->service,
                'daysUntilExpiry' => $daysUntilExpiry,
                'expiryDate' => $certificate->valid_until->format('d/m/Y'),
                'companyInfo' => [
                    'name' => 'EcoPlagas Soluciones',
                    'phone' => '+593 99 503 1066',
                    'email' => 'info@ecoplagasecuador.com',
                    'website' => 'www.ecoplagasecuador.com'
                ]
            ], function ($message) use ($certificate) {
                $message->to($certificate->user->email, $certificate->user->name)
                    ->subject('Recordatorio: Su certificado de control de plagas está próximo a vencer')
                    ->from(config('mail.from.address'), config('mail.from.name'));
            });

            Log::info('Certificate expiry email sent successfully', [
                'certificate_id' => $certificate->id,
                'certificate_number' => $certificate->certificate_number,
                'client_email' => $certificate->user->email,
                'days_until_expiry' => $daysUntilExpiry
            ]);

        } catch (\Exception $e) {
            Log::error('Error sending certificate expiry email', [
                'certificate_id' => $this->certificate->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Re-throw exception to trigger failed job handling
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Certificate expiry notification job failed', [
            'certificate_id' => $this->certificate->id,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }
}
