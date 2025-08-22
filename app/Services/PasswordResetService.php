<?php

namespace App\Services;

use App\Models\User;
use App\Models\AccountActivationToken;
use App\Services\EmailService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PasswordResetService
{
    protected EmailService $emailService;

    public function __construct(EmailService $emailService)
    {
        $this->emailService = $emailService;
    }

    /**
     * Send password reset email
     */
    public function sendPasswordResetLink(string $email)
    {
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            return ['success' => false, 'message' => 'No existe una cuenta con este correo electrónico'];
        }

        // Generate token
        $token = Str::random(64);
        
        // Store token in database
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            [
                'email' => $email,
                'token' => Hash::make($token),
                'created_at' => now(),
            ]
        );

        // Create reset URL
        $resetUrl = env('FRONTEND_URL', 'http://localhost:5173') . "/auth/reset-password?token={$token}&email=" . urlencode($email);
        
        // Send email using EmailService
        $emailResult = $this->emailService->sendPasswordResetEmail($user, $token, $resetUrl);
        
        return [
            'success' => true, 
            'message' => $emailResult['message'],
            'reset_url' => $emailResult['reset_url'] ?? null // For development when emails are disabled
        ];
    }

    /**
     * Reset password with token
     */
    public function resetPasswordWithToken(string $token, string $email, string $password)
    {
        $tokenRecord = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->first();

        if (!$tokenRecord) {
            return ['success' => false, 'message' => 'Token inválido'];
        }

        // Check if token is valid (not older than 1 hour)
        if (now()->diffInMinutes($tokenRecord->created_at) > 60) {
            // Delete expired token
            DB::table('password_reset_tokens')->where('email', $email)->delete();
            return ['success' => false, 'message' => 'El token ha expirado'];
        }

        // Verify token
        if (!Hash::check($token, $tokenRecord->token)) {
            return ['success' => false, 'message' => 'Token inválido'];
        }

        // Update password
        $user = User::where('email', $email)->first();
        if (!$user) {
            return ['success' => false, 'message' => 'Usuario no encontrado'];
        }

        $user->update([
            'password' => Hash::make($password),
            'email_verified_at' => $user->email_verified_at ?: now(), // Verify email if not verified
        ]);

        // Delete used token
        DB::table('password_reset_tokens')->where('email', $email)->delete();

        // Revoke all user tokens
        $user->tokens()->delete();

        return ['success' => true, 'message' => 'Contraseña restablecida exitosamente'];
    }

    /**
     * Send account activation email
     */
    public function sendAccountActivationLink(User $user)
    {
        if ($user->email_verified_at) {
            return ['success' => false, 'message' => 'La cuenta ya está activada'];
        }

        // Create activation token
        $tokenRecord = AccountActivationToken::createToken($user);
        
        $activationUrl = env('FRONTEND_URL', 'http://localhost:5173') . "/auth/activate-account?token={$tokenRecord->token}";
        
        // Send email using EmailService
        return $this->emailService->sendAccountActivationEmail($user, $tokenRecord->token, $activationUrl);
    }

    /**
     * Activate account with token
     */
    public function activateAccount(string $token, string $password)
    {
        $tokenRecord = AccountActivationToken::findValidToken($token);
        
        if (!$tokenRecord) {
            \Log::warning('Account activation failed: Invalid or expired token', ['token' => $token]);
            return ['success' => false, 'message' => 'Token inválido o expirado'];
        }

        $user = $tokenRecord->user;
        
        \Log::info('Starting account activation', [
            'user_id' => $user->id,
            'email' => $user->email,
            'token_id' => $tokenRecord->id,
            'email_verified_before' => $user->email_verified_at
        ]);
        
        try {
            // Use database transaction to ensure both operations succeed
            DB::transaction(function () use ($user, $password, $tokenRecord) {
                // Update user password and verify email
                $user->update([
                    'password' => Hash::make($password),
                    'email_verified_at' => now(),
                    'is_active' => true,
                ]);

                // Mark token as used
                $tokenRecord->markAsUsed();
            });

            // Refresh user to get updated data
            $user->refresh();
            
            \Log::info('Account activation successful', [
                'user_id' => $user->id,
                'email' => $user->email,
                'email_verified_after' => $user->email_verified_at
            ]);

            return [
                'success' => true, 
                'message' => 'Cuenta activada exitosamente',
                'user' => $user
            ];
        } catch (\Exception $e) {
            \Log::error('Account activation failed with exception', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false, 
                'message' => 'Error interno al activar la cuenta'
            ];
        }
    }

    /**
     * Validate token
     */
    public function validatePasswordResetToken(string $token, string $email)
    {
        $tokenRecord = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->first();

        if (!$tokenRecord) {
            return ['success' => false, 'message' => 'Token inválido'];
        }

        // Check if token is expired
        if (now()->diffInMinutes($tokenRecord->created_at) > 60) {
            DB::table('password_reset_tokens')->where('email', $email)->delete();
            return ['success' => false, 'message' => 'El token ha expirado'];
        }

        // Verify token
        if (!Hash::check($token, $tokenRecord->token)) {
            return ['success' => false, 'message' => 'Token inválido'];
        }

        return ['success' => true, 'message' => 'Token válido'];
    }

    /**
     * Validate activation token
     */
    public function validateActivationToken(string $token)
    {
        $tokenRecord = AccountActivationToken::findValidToken($token);
        
        if (!$tokenRecord) {
            return ['success' => false, 'message' => 'Token inválido o expirado'];
        }

        return [
            'success' => true, 
            'message' => 'Token válido',
            'user' => $tokenRecord->user
        ];
    }
}