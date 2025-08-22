<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class ProfileController extends Controller
{
    /**
     * Get client profile information
     */
    public function getProfile(): JsonResponse
    {
        try {
            $user = Auth::user();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'address' => $user->address,
                    'city' => $user->city,
                    'document_type' => $user->document_type,
                    'document_number' => $user->document_number,
                    'birth_date' => $user->birth_date,
                    'preferences' => $user->preferences ? json_decode($user->preferences, true) : [
                        'preferred_time_slot' => 'morning',
                        'allow_weekend_service' => false,
                        'emergency_contact' => '',
                        'emergency_contact_name' => '',
                        'special_instructions' => '',
                        'allergies_or_concerns' => ''
                    ],
                    'security_settings' => $user->security_settings ? json_decode($user->security_settings, true) : [
                        'session_timeout' => 30
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el perfil',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update client profile information
     */
    public function updateProfile(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'phone' => 'nullable|string|max:20',
                'address' => 'nullable|string|max:500',
                'city' => 'nullable|string|max:255',
                'document_type' => 'nullable|in:cedula,passport,ruc',
                'document_number' => 'nullable|string|max:50',
                'birth_date' => 'nullable|date',
                'preferences' => 'nullable|array',
                'preferences.preferred_time_slot' => 'nullable|in:morning,afternoon,evening',
                'preferences.allow_weekend_service' => 'nullable|boolean',
                'preferences.emergency_contact' => 'nullable|string|max:20',
                'preferences.emergency_contact_name' => 'nullable|string|max:255',
                'preferences.special_instructions' => 'nullable|string|max:1000',
                'preferences.allergies_or_concerns' => 'nullable|string|max:1000',
                'security_settings' => 'nullable|array',
                'security_settings.session_timeout' => 'nullable|integer|min:15|max:120'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de entrada inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }

            $updateData = [
                'name' => $request->name,
                'phone' => $request->phone,
                'address' => $request->address,
                'city' => $request->city,
                'document_type' => $request->document_type,
                'document_number' => $request->document_number,
                'birth_date' => $request->birth_date
            ];

            if ($request->has('preferences')) {
                $updateData['preferences'] = json_encode($request->preferences);
            }

            if ($request->has('security_settings')) {
                $updateData['security_settings'] = json_encode($request->security_settings);
            }

            $user->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Perfil actualizado exitosamente',
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'address' => $user->address,
                    'city' => $user->city,
                    'document_type' => $user->document_type,
                    'document_number' => $user->document_number,
                    'birth_date' => $user->birth_date,
                    'preferences' => $user->preferences ? json_decode($user->preferences, true) : null,
                    'security_settings' => $user->security_settings ? json_decode($user->security_settings, true) : null
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el perfil',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Change user password
     */
    public function changePassword(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            
            $validator = Validator::make($request->all(), [
                'current_password' => 'required|string',
                'new_password' => 'required|string|min:6|confirmed',
                'new_password_confirmation' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de entrada inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Verify current password
            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'La contraseña actual es incorrecta'
                ], 422);
            }

            // Update password
            $user->update([
                'password' => Hash::make($request->new_password)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Contraseña cambiada exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar la contraseña',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}