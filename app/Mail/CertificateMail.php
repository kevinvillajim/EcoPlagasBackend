<?php

namespace App\Mail;

use App\Models\Certificate;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Attachment;
use Barryvdh\DomPDF\Facade\Pdf;

class CertificateMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $user;
    public $certificate;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, Certificate $certificate)
    {
        $this->user = $user;
        $this->certificate = $certificate;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $subject = 'Certificado de Control de Plagas - ' . $this->certificate->certificate_number;

        $mail = $this->subject($subject)
                     ->view('emails.certificate')
                     ->with([
                         'userName' => $this->user->name,
                         'certificateNumber' => $this->certificate->certificate_number,
                         'certificateType' => $this->certificate->type,
                         'issueDate' => $this->certificate->issue_date->format('d/m/Y'),
                         'validUntil' => $this->certificate->valid_until->format('d/m/Y'),
                         'serviceType' => $this->certificate->service->type ?? 'Control de Plagas',
                         'companyName' => config('app.name', 'EcoPlagas'),
                     ]);

        // Adjuntar PDF del certificado
        try {
            $service = $this->certificate->service;
            
            $pdf = Pdf::loadView('pdf.certificate', [
                'certificate' => $this->certificate,
                'service' => $service
            ])
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isRemoteEnabled' => false,
                'isHtml5ParserEnabled' => true,
                'chroot' => resource_path('views'),
                'defaultFont' => 'Arial'
            ]);

            $pdfContent = $pdf->output();
            $filename = 'certificado_' . $this->certificate->certificate_number . '.pdf';
            
            $mail->attachData($pdfContent, $filename, [
                'mime' => 'application/pdf',
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error generating PDF for email attachment', [
                'certificate_id' => $this->certificate->id,
                'error' => $e->getMessage()
            ]);
            // Continuar enviando el email sin el adjunto
        }

        return $mail;
    }
}