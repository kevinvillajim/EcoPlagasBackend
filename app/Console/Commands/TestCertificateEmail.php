<?php

namespace App\Console\Commands;

use App\Jobs\SendCertificateExpiryNotificationJob;
use App\Models\Certificate;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class TestCertificateEmail extends Command
{
    protected $signature = 'test:certificate-email {certificate_id}';
    protected $description = 'Test certificate expiry email sending';

    public function handle()
    {
        $certificateId = $this->argument('certificate_id');
        
        $this->info("Testing certificate email for ID: {$certificateId}");
        
        $certificate = Certificate::with(['user', 'service'])->find($certificateId);
        
        if (!$certificate) {
            $this->error("Certificate not found!");
            return 1;
        }
        
        $this->info("Certificate found: {$certificate->certificate_number}");
        $this->info("Client: {$certificate->user->name} ({$certificate->user->email})");
        
        try {
            // Test 1: Direct email send (no queue)
            $this->info("Testing direct email send...");
            
            $daysUntilExpiry = (int) round(now()->diffInDays($certificate->valid_until, false));
            
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
                    ->subject('TEST: Recordatorio de certificado próximo a vencer')
                    ->from(config('mail.from.address'), config('mail.from.name'));
            });
            
            $this->info("✅ Direct email sent successfully!");
            
        } catch (\Exception $e) {
            $this->error("❌ Direct email failed: " . $e->getMessage());
            Log::error('Test certificate email failed', [
                'certificate_id' => $certificateId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
        
        try {
            // Test 2: Job dispatch
            $this->info("Testing job dispatch...");
            SendCertificateExpiryNotificationJob::dispatch($certificate);
            $this->info("✅ Job dispatched successfully!");
            
        } catch (\Exception $e) {
            $this->error("❌ Job dispatch failed: " . $e->getMessage());
        }
        
        return 0;
    }
}