<?php

namespace App\Mail;

use App\Models\Service;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ServiceReminderMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $user;
    public $service;
    public $reminderType; // 'three_days' or 'same_day'

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, Service $service, string $reminderType)
    {
        $this->user = $user;
        $this->service = $service;
        $this->reminderType = $reminderType;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $subject = $this->reminderType === 'three_days' 
            ? 'Recordatorio: Servicio programado para el ' . $this->service->scheduled_date->format('d/m/Y')
            : 'Hoy: Servicio de control de plagas programado';

        return $this->subject($subject)
                    ->view('emails.service-reminder')
                    ->with([
                        'userName' => $this->user->name,
                        'serviceType' => $this->service->type,
                        'serviceDate' => $this->service->scheduled_date->format('d/m/Y'),
                        'serviceTime' => $this->service->scheduled_date->format('H:i'),
                        'serviceDescription' => $this->service->description,
                        'serviceAddress' => $this->service->address,
                        'reminderType' => $this->reminderType,
                        'contactPhone' => config('app.contact_phone', '0999-123-456'),
                        'contactEmail' => config('mail.from.address'),
                        'companyName' => config('app.name', 'EcoPlagas'),
                    ]);
    }
}