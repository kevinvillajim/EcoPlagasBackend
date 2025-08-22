<?php

namespace App\Services;

use App\Models\User;
use App\Services\PasswordResetService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthService
{
    /**
     * Authenticate user and generate token
     */
    public function login(array $credentials)
    {
        $remember = $credentials['remember'] ?? false;
        
        // Remove remember from credentials for authentication
        unset($credentials['remember']);
        
        // Attempt authentication
        if (!Auth::attempt($credentials, $remember)) {
            throw ValidationException::withMessages([
                'email' => ['Las credenciales proporcionadas son incorrectas.'],
            ]);
        }
        
        $user = Auth::user();
        
        // Check if user is active
        if (!$user->is_active) {
            Auth::logout();
            throw ValidationException::withMessages([
                'email' => ['Tu cuenta está desactivada. Contacta al administrador.'],
            ]);
        }
        
        // Revoke all existing tokens
        $user->tokens()->delete();
        
        // Create new token
        $token = $user->createToken('auth_token')->plainTextToken;
        
        return [
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer'
        ];
    }

    /**
     * Create new user (Admin only)
     */
    public function register(array $userData)
    {
        // Generate a secure random temporary password
        // This password will be replaced when the user activates their account
        $tempPassword = $userData['password'] ?? \Illuminate\Support\Str::random(32);
        
        // Set default values
        $userData['is_active'] = true; // Account is active but not verified
        $userData['role'] = $userData['role'] ?? 'client';
        $userData['password'] = Hash::make($tempPassword); // Temporary password (will be replaced on activation)
        $userData['email_verified_at'] = null; // Not verified until they activate
        
        // Create user
        $user = User::create($userData);
        
        // Send activation email
        $passwordResetService = app(PasswordResetService::class);
        $activationResult = $passwordResetService->sendAccountActivationLink($user);
        
        return [
            'user' => $user,
            'activation_result' => $activationResult
        ];
    }

    /**
     * Logout user
     */
    public function logout(User $user)
    {
        // Revoke all user tokens
        $user->tokens()->delete();
        
        return true;
    }

    /**
     * Refresh user token
     */
    public function refreshToken(User $user)
    {
        // Revoke current token
        $user->currentAccessToken()->delete();
        
        // Create new token
        $token = $user->createToken('auth_token')->plainTextToken;
        
        return [
            'token' => $token,
            'token_type' => 'Bearer'
        ];
    }

    /**
     * Send password reset email
     */
    public function forgotPassword(string $email)
    {
        $passwordResetService = app(PasswordResetService::class);
        return $passwordResetService->sendPasswordResetLink($email);
    }

    /**
     * Reset user password
     */
    public function resetPassword(string $token, string $email, string $password)
    {
        $passwordResetService = app(PasswordResetService::class);
        return $passwordResetService->resetPasswordWithToken($token, $email, $password);
    }

    /**
     * Update user profile
     */
    public function updateProfile(User $user, array $data)
    {
        // Remove sensitive fields that shouldn't be updated via this method
        unset($data['password'], $data['role'], $data['email']);
        
        $user->update($data);
        
        return $user->fresh();
    }

    /**
     * Change user password
     */
    public function changePassword(User $user, string $currentPassword, string $newPassword)
    {
        if (!Hash::check($currentPassword, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['La contraseña actual es incorrecta.'],
            ]);
        }
        
        $user->update([
            'password' => Hash::make($newPassword)
        ]);
        
        // Revoke all tokens to force re-authentication
        $user->tokens()->delete();
        
        return true;
    }
}