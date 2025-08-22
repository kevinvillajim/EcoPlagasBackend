<?php

namespace App\Services;

use App\Mail\AccountActivationMail;
use App\Mail\PasswordResetMail;
use App\Mail\ServiceConfirmationMail;
use App\Mail\ServiceDateChangeMail;
use App\Models\User;
use App\Models\Service;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EmailService
{
    /**
     * Check if email sending is enabled
     */
    private function isEmailEnabled(): bool
    {
        return config('mail.enabled', false);
    }

    /**
     * Send account activation email
     */
    public function sendAccountActivationEmail(User $user, string $token, string $activationUrl): array
    {
        $subject = 'Activación de Cuenta - EcoPlagas Ecuador';
        
        if ($this->isEmailEnabled()) {
            try {
                Mail::to($user->email)->send(new AccountActivationMail($user, $token, $activationUrl));
                
                return [
                    'success' => true,
                    'message' => 'Email de activación enviado exitosamente',
                    'method' => 'email'
                ];
            } catch (\Exception $e) {
                Log::error('Error sending activation email', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'error' => $e->getMessage()
                ]);
                
                // Fallback to logging
                $this->logActivationLink($user, $activationUrl);
                
                return [
                    'success' => false,
                    'message' => 'Error al enviar email, enlace disponible en logs',
                    'method' => 'log_fallback',
                    'activation_url' => $activationUrl
                ];
            }
        } else {
            // Email disabled - log the link
            $this->logActivationLink($user, $activationUrl);
            
            return [
                'success' => true,
                'message' => 'Enlace de activación generado (ver logs para testing)',
                'method' => 'log',
                'activation_url' => $activationUrl // For development only
            ];
        }
    }

    /**
     * Send password reset email
     */
    public function sendPasswordResetEmail(User $user, string $token, string $resetUrl): array
    {
        $subject = 'Recuperación de Contraseña - EcoPlagas Ecuador';
        
        if ($this->isEmailEnabled()) {
            try {
                Mail::to($user->email)->send(new PasswordResetMail($user, $token, $resetUrl));
                
                return [
                    'success' => true,
                    'message' => 'Email de recuperación enviado exitosamente',
                    'method' => 'email'
                ];
            } catch (\Exception $e) {
                Log::error('Error sending password reset email', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'error' => $e->getMessage()
                ]);
                
                // Fallback to logging
                $this->logPasswordResetLink($user, $resetUrl);
                
                return [
                    'success' => false,
                    'message' => 'Error al enviar email, enlace disponible en logs',
                    'method' => 'log_fallback',
                    'reset_url' => $resetUrl
                ];
            }
        } else {
            // Email disabled - log the link
            $this->logPasswordResetLink($user, $resetUrl);
            
            return [
                'success' => true,
                'message' => 'Enlace de recuperación generado (ver logs para testing)',
                'method' => 'log',
                'reset_url' => $resetUrl // For development only
            ];
        }
    }

    /**
     * Log activation link for development/testing
     */
    private function logActivationLink(User $user, string $activationUrl): void
    {
        Log::info('===== ACCOUNT ACTIVATION EMAIL =====');
        Log::info("Para: {$user->email} ({$user->name})");
        Log::info("Asunto: Activación de Cuenta - EcoPlagas Ecuador");
        Log::info("Enlace de activación: {$activationUrl}");
        Log::info("=====================================");
    }

    /**
     * Log password reset link for development/testing
     */
    private function logPasswordResetLink(User $user, string $resetUrl): void
    {
        Log::info('===== PASSWORD RESET EMAIL =====');
        Log::info("Para: {$user->email} ({$user->name})");
        Log::info("Asunto: Recuperación de Contraseña - EcoPlagas Ecuador");
        Log::info("Enlace de recuperación: {$resetUrl}");
        Log::info("=================================");
    }

    /**
     * Get current email configuration status
     */
    public function getEmailStatus(): array
    {
        return [
            'enabled' => $this->isEmailEnabled(),
            'mailer' => config('mail.default'),
            'host' => config('mail.mailers.smtp.host'),
            'from' => config('mail.from.address'),
            'environment' => app()->environment()
        ];
    }

    /**
     * Send service confirmation email
     */
    public function sendServiceConfirmationEmail(User $user, Service $service): array
    {
        if ($this->isEmailEnabled()) {
            try {
                Mail::to($user->email)->send(new ServiceConfirmationMail($user, $service));
                
                Log::info('Service confirmation email sent', [
                    'user_id' => $user->id,
                    'service_id' => $service->id,
                    'email' => $user->email
                ]);
                
                return [
                    'success' => true,
                    'message' => 'Email de confirmación enviado exitosamente',
                    'method' => 'email'
                ];
            } catch (\Exception $e) {
                Log::error('Error sending service confirmation email', [
                    'user_id' => $user->id,
                    'service_id' => $service->id,
                    'email' => $user->email,
                    'error' => $e->getMessage()
                ]);
                
                return [
                    'success' => false,
                    'message' => 'Error al enviar email de confirmación',
                    'method' => 'email_failed'
                ];
            }
        } else {
            // Email disabled - log confirmation
            Log::info('Service confirmation email (disabled mode)', [
                'user_id' => $user->id,
                'service_id' => $service->id,
                'user_name' => $user->name,
                'service_type' => $service->type,
                'service_date' => $service->scheduled_date,
                'service_time' => $service->scheduled_time,
                'message' => 'Email de confirmación generado (emails deshabilitados)'
            ]);
            
            return [
                'success' => true,
                'message' => 'Email de confirmación generado (ver logs para testing)',
                'method' => 'log'
            ];
        }
    }

    /**
     * Send service date/time change notification email
     */
    public function sendServiceDateChangeEmail(User $user, Service $service, string $oldDate = null, string $oldTime = null): array
    {
        if ($this->isEmailEnabled()) {
            try {
                Mail::to($user->email)->send(new ServiceDateChangeMail($user, $service, $oldDate, $oldTime));
                
                Log::info('Service date change email sent', [
                    'user_id' => $user->id,
                    'service_id' => $service->id,
                    'email' => $user->email,
                    'old_date' => $oldDate,
                    'old_time' => $oldTime,
                    'new_date' => $service->scheduled_date,
                    'new_time' => $service->scheduled_time
                ]);
                
                return [
                    'success' => true,
                    'message' => 'Email de cambio de fecha enviado exitosamente',
                    'method' => 'email'
                ];
            } catch (\Exception $e) {
                Log::error('Error sending service date change email', [
                    'user_id' => $user->id,
                    'service_id' => $service->id,
                    'email' => $user->email,
                    'error' => $e->getMessage()
                ]);
                
                return [
                    'success' => false,
                    'message' => 'Error al enviar email de cambio de fecha',
                    'method' => 'email_failed'
                ];
            }
        } else {
            // Email disabled - log notification
            Log::info('Service date change email (disabled mode)', [
                'user_id' => $user->id,
                'service_id' => $service->id,
                'user_name' => $user->name,
                'service_type' => $service->type,
                'old_date' => $oldDate,
                'old_time' => $oldTime,
                'new_date' => $service->scheduled_date,
                'new_time' => $service->scheduled_time,
                'message' => 'Email de cambio de fecha generado (emails deshabilitados)'
            ]);
            
            return [
                'success' => true,
                'message' => 'Email de cambio de fecha generado (ver logs para testing)',
                'method' => 'log'
            ];
        }
    }

    /**
     * Send emergency service email to client
     */
    public function sendEmergencyServiceEmail(User $user, Service $service, string $emergencyType): array
    {
        if ($this->isEmailEnabled()) {
            try {
                // Use service confirmation mail for now, can create specific template later
                Mail::to($user->email)->send(new ServiceConfirmationMail($user, $service));
                
                Log::info('Emergency service email sent to client', [
                    'user_id' => $user->id,
                    'service_id' => $service->id,
                    'emergency_type' => $emergencyType,
                    'email' => $user->email
                ]);
                
                return [
                    'success' => true,
                    'message' => 'Email de servicio de emergencia enviado al cliente',
                    'method' => 'email'
                ];
            } catch (\Exception $e) {
                Log::error('Error sending emergency service email to client', [
                    'user_id' => $user->id,
                    'service_id' => $service->id,
                    'emergency_type' => $emergencyType,
                    'error' => $e->getMessage()
                ]);
                
                return [
                    'success' => false,
                    'message' => 'Error al enviar email de emergencia al cliente',
                    'method' => 'email_failed'
                ];
            }
        } else {
            // Email disabled - log confirmation
            Log::info('Emergency service email to client (disabled mode)', [
                'user_id' => $user->id,
                'service_id' => $service->id,
                'emergency_type' => $emergencyType,
                'user_name' => $user->name,
                'service_type' => $service->type
            ]);
            
            return [
                'success' => true,
                'message' => 'Email de emergencia al cliente generado (ver logs)',
                'method' => 'log'
            ];
        }
    }

    /**
     * Send emergency notification email to administrators
     */
    public function sendEmergencyAdminNotificationEmail(User $admin, Service $service, User $client, string $emergencyType): array
    {
        $subject = $emergencyType === 'immediate' 
            ? '🚨 EMERGENCIA INMEDIATA - EcoPlagas' 
            : '⚠️ Servicio de Emergencia Fuera de Horario - EcoPlagas';

        $urgencyLevel = $emergencyType === 'immediate' ? 'CRÍTICA' : 'ALTA';
        $responseTime = $emergencyType === 'immediate' ? '15 minutos' : '2 horas';
        $attentionType = $emergencyType === 'immediate' ? 'INMEDIATA' : 'prioritaria';
        $actionRequired = $emergencyType === 'immediate' 
            ? "⚡ CONTACTAR AL CLIENTE INMEDIATAMENTE (máximo {$responseTime})"
            : "📞 Contactar al cliente dentro de las próximas {$responseTime}";
        
        $emailBody = "ALERTA DE SERVICIO DE EMERGENCIA - PRIORIDAD {$urgencyLevel}

Un cliente ha solicitado un servicio de emergencia que requiere atención {$attentionType}.

DATOS DEL CLIENTE:
- Nombre: {$client->name}
- Teléfono: {$client->phone}
- Email: {$client->email}

DETALLES DEL SERVICIO:
- Tipo: {$service->type}
- Dirección: {$service->address}
- Descripción del problema: {$service->description}
- Fecha/Hora de solicitud: " . now()->format('d/m/Y H:i') . "

ACCIÓN REQUERIDA:
{$actionRequired}

INSTRUCCIONES:
1. Llamar al cliente al {$client->phone}
2. Evaluar la urgencia de la situación
3. Coordinar la visita técnica
4. Actualizar el estado del servicio en el sistema

---
Sistema EcoPlagas - Notificación Automática";

        if ($this->isEmailEnabled()) {
            try {
                Mail::raw($emailBody, function ($message) use ($admin, $subject) {
                    $message->to($admin->email)
                            ->subject($subject)
                            ->from(config('mail.from.address'), config('mail.from.name'));
                });
                
                Log::info('Emergency notification email sent to admin', [
                    'admin_id' => $admin->id,
                    'service_id' => $service->id,
                    'client_id' => $client->id,
                    'emergency_type' => $emergencyType,
                    'admin_email' => $admin->email
                ]);
                
                return [
                    'success' => true,
                    'message' => 'Email de emergencia enviado al administrador',
                    'method' => 'email'
                ];
            } catch (\Exception $e) {
                Log::error('Error sending emergency notification email to admin', [
                    'admin_id' => $admin->id,
                    'service_id' => $service->id,
                    'client_id' => $client->id,
                    'emergency_type' => $emergencyType,
                    'error' => $e->getMessage()
                ]);
                
                return [
                    'success' => false,
                    'message' => 'Error al enviar email de emergencia al admin',
                    'method' => 'email_failed'
                ];
            }
        } else {
            // Email disabled - log notification
            Log::info('=== EMERGENCY ADMIN NOTIFICATION EMAIL ===');
            Log::info("Para: {$admin->email} ({$admin->name})");
            Log::info("Asunto: {$subject}");
            Log::info("Contenido:\n{$emailBody}");
            Log::info('==========================================');
            
            return [
                'success' => true,
                'message' => 'Email de emergencia al admin generado (ver logs)',
                'method' => 'log'
            ];
        }
    }
}