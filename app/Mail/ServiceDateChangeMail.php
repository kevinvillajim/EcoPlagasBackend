<?php

namespace App\Mail;

use App\Models\User;
use App\Models\Service;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ServiceDateChangeMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $service;
    public $oldDate;
    public $oldTime;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, Service $service, string $oldDate = null, string $oldTime = null)
    {
        $this->user = $user;
        $this->service = $service;
        $this->oldDate = $oldDate;
        $this->oldTime = $oldTime;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->view('emails.service-date-change')
                    ->subject('Cambio de Fecha/Hora de su Servicio - EcoPlagas Ecuador')
                    ->with([
                        'user' => $this->user,
                        'service' => $this->service,
                        'oldDate' => $this->oldDate,
                        'oldTime' => $this->oldTime,
                        'companyName' => 'EcoPlagas Ecuador',
                        'companyPhone' => '+593 99 123 4567',
                        'companyEmail' => 'info@ecoplagas.ec'
                    ]);
    }
}