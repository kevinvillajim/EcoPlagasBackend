<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Traits\ErrorHandler;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use App\Services\PasswordResetService;
use App\Services\EmailService;
use App\Http\Traits\CorsResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    use ErrorHandler;
    use CorsResponse;

    protected AuthService $authService;
    protected PasswordResetService $passwordResetService;
    protected EmailService $emailService;

    public function __construct(
        AuthService $authService, 
        PasswordResetService $passwordResetService,
        EmailService $emailService
    ) {
        $this->authService = $authService;
        $this->passwordResetService = $passwordResetService;
        $this->emailService = $emailService;
    }

    /**
     * Login user
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $result = $this->authService->login($request->validated());
            
            return $this->corsResponse([
                'success' => true,
                'message' => '¡Bienvenido de vuelta!',
                'user' => new UserResource($result['user']),
                'token' => $result['token'],
                'token_type' => $result['token_type']
            ]);
        } catch (ValidationException $e) {
            return $this->corsResponse([
                'success' => false,
                'message' => 'Error de autenticación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return $this->corsResponse([
                'success' => false,
                'message' => 'Error inesperado al iniciar sesión'
            ], 500);
        }
    }

    /**
     * Register user (Admin only)
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $result = $this->authService->register($request->validated());
            
            return response()->json([
                'success' => true,
                'message' => 'Usuario creado exitosamente. Se ha enviado un enlace de activación al correo electrónico.',
                'user' => new UserResource($result['user']),
                'activation_info' => $result['activation_result']
            ], 201);
        } catch (\Exception $e) {
            return $this->handleException($e, 'Error al crear usuario');
        }
    }

    /**
     * Logout user
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            $this->authService->logout($request->user());
            
            return response()->json([
                'success' => true,
                'message' => 'Sesión cerrada exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cerrar sesión'
            ], 500);
        }
    }

    /**
     * Refresh token
     */
    public function refreshToken(Request $request): JsonResponse
    {
        try {
            $result = $this->authService->refreshToken($request->user());
            
            return response()->json([
                'success' => true,
                'message' => 'Token renovado exitosamente',
                'token' => $result['token'],
                'token_type' => $result['token_type']
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al renovar token'
            ], 500);
        }
    }

    /**
     * Forgot password
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email|exists:users,email'
        ], [
            'email.required' => 'El correo electrónico es requerido',
            'email.email' => 'El correo electrónico debe ser válido',
            'email.exists' => 'No existe una cuenta con este correo electrónico'
        ]);

        try {
            $result = $this->authService->forgotPassword($request->email);
            
            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => $result['message'],
                    'reset_url' => $result['reset_url'] ?? null // For development only
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar solicitud de recuperación'
            ], 500);
        }
    }

    /**
     * Reset password
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'token' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|string|min:6|confirmed'
        ], [
            'token.required' => 'El token es requerido',
            'email.required' => 'El correo electrónico es requerido',
            'password.required' => 'La contraseña es requerida',
            'password.min' => 'La contraseña debe tener al menos 6 caracteres',
            'password.confirmed' => 'Las contraseñas no coinciden'
        ]);

        try {
            $result = $this->authService->resetPassword($request->token, $request->email, $request->password);
            
            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => $result['message']
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al restablecer contraseña'
            ], 500);
        }
    }

    /**
     * Activate account
     */
    public function activateAccount(Request $request): JsonResponse
    {
        $request->validate([
            'token' => 'required|string',
            'password' => 'required|string|min:6|confirmed'
        ], [
            'token.required' => 'El token es requerido',
            'password.required' => 'La contraseña es requerida',
            'password.min' => 'La contraseña debe tener al menos 6 caracteres',
            'password.confirmed' => 'Las contraseñas no coinciden'
        ]);

        try {
            $result = $this->passwordResetService->activateAccount($request->token, $request->password);
            
            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => $result['message'],
                    'user' => new UserResource($result['user'])
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al activar cuenta'
            ], 500);
        }
    }

    /**
     * Validate password reset token
     */
    public function validateResetToken(Request $request): JsonResponse
    {
        $request->validate([
            'token' => 'required|string',
            'email' => 'required|email',
        ]);

        try {
            $result = $this->passwordResetService->validatePasswordResetToken($request->token, $request->email);
            
            return response()->json([
                'success' => $result['success'],
                'message' => $result['message']
            ], $result['success'] ? 200 : 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al validar token'
            ], 500);
        }
    }

    /**
     * Validate activation token
     */
    public function validateActivationToken(Request $request): JsonResponse
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        try {
            $result = $this->passwordResetService->validateActivationToken($request->token);
            
            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'],
                'user' => isset($result['user']) ? new UserResource($result['user']) : null
            ], $result['success'] ? 200 : 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al validar token'
            ], 500);
        }
    }

    /**
     * Get current user
     */
    public function me(Request $request): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'user' => new UserResource($request->user())
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener información del usuario'
            ], 500);
        }
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'avatar' => 'nullable|string|max:255'
        ], [
            'name.required' => 'El nombre es requerido',
            'name.max' => 'El nombre no debe exceder 255 caracteres',
            'phone.max' => 'El teléfono no debe exceder 20 caracteres',
            'address.max' => 'La dirección no debe exceder 500 caracteres'
        ]);

        try {
            $user = $this->authService->updateProfile($request->user(), $request->validated());
            
            return response()->json([
                'success' => true,
                'message' => 'Perfil actualizado exitosamente',
                'user' => new UserResource($user)
            ]);
        } catch (\Exception $e) {
            return $this->handleException($e, 'Error al actualizar perfil');
        }
    }

    /**
     * Change user password
     */
    public function changePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6|confirmed',
        ], [
            'current_password.required' => 'La contraseña actual es requerida',
            'new_password.required' => 'La nueva contraseña es requerida',
            'new_password.min' => 'La nueva contraseña debe tener al menos 6 caracteres',
            'new_password.confirmed' => 'Las contraseñas no coinciden'
        ]);

        try {
            $this->authService->changePassword(
                $request->user(), 
                $request->current_password, 
                $request->new_password
            );
            
            return response()->json([
                'success' => true,
                'message' => 'Contraseña actualizada exitosamente. Por favor, inicia sesión nuevamente.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get email system status (Admin only)
     */
    public function getEmailStatus(Request $request): JsonResponse
    {
        try {
            $status = $this->emailService->getEmailStatus();
            
            return response()->json([
                'success' => true,
                'status' => $status
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estado del sistema de emails'
            ], 500);
        }
    }
}