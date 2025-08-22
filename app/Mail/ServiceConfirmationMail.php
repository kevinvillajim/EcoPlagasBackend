<?php

namespace App\Mail;

use App\Models\Service;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ServiceConfirmationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $user;
    public $service;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, Service $service)
    {
        $this->user = $user;
        $this->service = $service;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $subject = '✅ Confirmación de Servicio Programado - EcoPlagas Ecuador';

        return $this->subject($subject)
                    ->view('emails.service-confirmation')
                    ->with([
                        'userName' => $this->user->name,
                        'serviceType' => $this->service->type,
                        'serviceDate' => $this->service->scheduled_date,
                        'serviceTime' => $this->service->scheduled_time,
                        'serviceDescription' => $this->service->description,
                        'serviceAddress' => $this->service->address,
                        'serviceId' => $this->service->id,
                        'contactPhone' => config('app.contact_phone', '0999-123-456'),
                        'contactEmail' => config('mail.from.address'),
                        'companyName' => config('app.name', 'EcoPlagas'),
                    ]);
    }
}
