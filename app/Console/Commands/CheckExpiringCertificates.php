<?php

namespace App\Console\Commands;

use App\Jobs\SendCertificateExpiryNotificationJob;
use App\Models\Certificate;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckExpiringCertificates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'certificates:check-expiring {--days=7 : Number of days before expiry to send notification}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for certificates expiring soon and send notifications';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = (int) $this->option('days');
        $expiryDate = now()->addDays($days);
        
        $this->info("Checking for certificates expiring in the next {$days} days...");
        
        // Find certificates expiring soon that are still valid
        $expiringCertificates = Certificate::with(['user', 'service'])
            ->where('status', Certificate::STATUS_VALID)
            ->where('valid_until', '<=', $expiryDate)
            ->where('valid_until', '>=', now())
            ->get();

        if ($expiringCertificates->isEmpty()) {
            $this->info('No certificates found expiring in the next ' . $days . ' days.');
            return 0;
        }

        $this->info("Found {$expiringCertificates->count()} certificates expiring soon.");

        foreach ($expiringCertificates as $certificate) {
            try {
                // Check if we already sent a notification for this certificate (EVER)
                $existingNotification = Notification::where('type', Notification::TYPE_CERTIFICATE_EXPIRING)
                    ->where('data', 'LIKE', '%"certificate_id":' . $certificate->id . '%')
                    ->first();

                if ($existingNotification) {
                    $this->info("Notification already sent for certificate: {$certificate->certificate_number}");
                    continue;
                }

                $daysUntilExpiry = (int) round(now()->diffInDays($certificate->valid_until, false));

                // Create notification for ALL admins
                $admins = User::where('role', 'admin')->get();
                foreach ($admins as $admin) {
                    Notification::create([
                        'user_id' => $admin->id,
                        'type' => Notification::TYPE_CERTIFICATE_EXPIRING,
                        'title' => 'Certificado próximo a vencer',
                        'message' => "El certificado {$certificate->certificate_number} del cliente {$certificate->user->name} vence el " . $certificate->valid_until->format('d/m/Y'),
                        'data' => [
                            'certificate_id' => $certificate->id,
                            'certificate_number' => $certificate->certificate_number,
                            'client_name' => $certificate->user->name,
                            'client_id' => $certificate->user_id,
                            'expiry_date' => $certificate->valid_until->toDateString(),
                            'days_until_expiry' => $daysUntilExpiry
                        ],
                        'read_at' => null,
                    ]);
                }

                // Create notification for client
                Notification::create([
                    'user_id' => $certificate->user_id,
                    'type' => Notification::TYPE_CERTIFICATE_EXPIRING,
                    'title' => 'Su certificado está próximo a vencer',
                    'message' => "Su certificado de control de plagas {$certificate->certificate_number} vencerá el " . $certificate->valid_until->format('d/m/Y') . '. Contacte con nosotros para renovar el servicio.',
                    'data' => [
                        'certificate_id' => $certificate->id,
                        'certificate_number' => $certificate->certificate_number,
                        'expiry_date' => $certificate->valid_until->toDateString(),
                        'days_until_expiry' => $daysUntilExpiry
                    ],
                    'read_at' => null,
                ]);

                // Send email directly to client
                try {
                    $daysUntilExpiry = (int) round(now()->diffInDays($certificate->valid_until, false));
                    
                    \Illuminate\Support\Facades\Mail::send('emails.certificate-expiry', [
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
                } catch (\Exception $emailError) {
                    Log::error('Error sending certificate expiry email directly', [
                        'certificate_id' => $certificate->id,
                        'error' => $emailError->getMessage()
                    ]);
                }

                $this->info("Notifications sent to client and {$admins->count()} admin(s) for certificate: {$certificate->certificate_number}");
                
                Log::info('Certificate expiry notifications sent', [
                    'certificate_id' => $certificate->id,
                    'certificate_number' => $certificate->certificate_number,
                    'client_email' => $certificate->user->email,
                    'admin_count' => $admins->count(),
                    'expiry_date' => $certificate->valid_until->toDateString()
                ]);

            } catch (\Exception $e) {
                $this->error("Error processing certificate {$certificate->certificate_number}: " . $e->getMessage());
                
                Log::error('Error sending certificate expiry notification', [
                    'certificate_id' => $certificate->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        $this->info("Processed {$expiringCertificates->count()} expiring certificates.");
        return 0;
    }
}
