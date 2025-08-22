<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:test {email?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test email configuration by sending a test email';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email') ?? 'kevinvillajim@hotmail.com';
        
        $this->info('Testing email configuration...');
        $this->info('MAIL_ENABLED: ' . config('mail.enabled', 'not set'));
        $this->info('MAIL_MAILER: ' . config('mail.default'));
        $this->info('MAIL_HOST: ' . config('mail.mailers.smtp.host'));
        $this->info('MAIL_FROM: ' . config('mail.from.address'));
        
        try {
            Mail::raw('This is a test email from EcoPlagas system. If you receive this, email configuration is working correctly!', function($message) use ($email) {
                $message->to($email)
                    ->subject('Test Email from EcoPlagas');
            });
            
            $this->info('✅ Email sent successfully to: ' . $email);
            $this->info('Check your inbox (and spam folder) for the test email.');
        } catch (\Exception $e) {
            $this->error('❌ Failed to send email: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
        }
        
        return Command::SUCCESS;
    }
}