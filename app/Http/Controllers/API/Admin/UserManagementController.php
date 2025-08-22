<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class UserManagementController extends Controller
{
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Get all users with pagination and filtering
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 15);
            $search = $request->get('search');
            $role = $request->get('role');
            $status = $request->get('status');

            $query = User::query()->latest();

            // Search by name or email
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            // Filter by role
            if ($role) {
                $query->where('role', $role);
            }

            // Filter by status
            if ($status !== null) {
                $query->where('is_active', $status === 'active');
            }

            $users = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'users' => UserResource::collection($users->items()),
                'pagination' => [
                    'current_page' => $users->currentPage(),
                    'total_pages' => $users->lastPage(),
                    'per_page' => $users->perPage(),
                    'total' => $users->total(),
                    'from' => $users->firstItem(),
                    'to' => $users->lastItem(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener usuarios'
            ], 500);
        }
    }

    /**
     * Create new user (alternative to register endpoint)
     */
    public function store(RegisterRequest $request): JsonResponse
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
            return response()->json([
                'success' => false,
                'message' => 'Error al crear usuario: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get specific user
     */
    public function show(Request $request, $id): JsonResponse
    {
        try {
            $user = User::findOrFail($id);
            
            return response()->json([
                'success' => true,
                'user' => new UserResource($user)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no encontrado'
            ], 404);
        }
    }

    /**
     * Update user information
     */
    public function update(Request $request, $id): JsonResponse
    {
        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => ['sometimes', 'required', 'email', 'max:255', Rule::unique('users')->ignore($id)],
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'role' => ['sometimes', 'required', Rule::in(['client', 'admin'])],
        ], [
            'name.required' => 'El nombre es requerido',
            'email.required' => 'El correo electrónico es requerido',
            'email.unique' => 'Este correo electrónico ya está registrado',
            'role.in' => 'El rol debe ser client o admin'
        ]);

        try {
            $user = User::findOrFail($id);
            
            // Prevent self role modification for security
            if ($request->user()->id == $id && $request->has('role')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No puedes cambiar tu propio rol'
                ], 403);
            }

            $user->update($request->only([
                'name', 'email', 'phone', 'address', 'role'
            ]));
            
            return response()->json([
                'success' => true,
                'message' => 'Usuario actualizado exitosamente',
                'user' => new UserResource($user->fresh())
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar usuario'
            ], 500);
        }
    }

    /**
     * Delete user
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        try {
            $user = User::findOrFail($id);
            
            // Prevent self deletion
            if ($request->user()->id == $id) {
                return response()->json([
                    'success' => false,
                    'message' => 'No puedes eliminar tu propia cuenta'
                ], 403);
            }

            // TODO: Check if user has services when Service model is implemented
            // if ($user->services()->exists()) {
            //     return response()->json([
            //         'success' => false,
            //         'message' => 'No se puede eliminar el usuario porque tiene servicios asociados'
            //     ], 400);
            // }

            $user->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Usuario eliminado exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar usuario'
            ], 500);
        }
    }

    /**
     * Change user active status
     */
    public function updateStatus(Request $request, $id): JsonResponse
    {
        $request->validate([
            'is_active' => 'required|boolean'
        ], [
            'is_active.required' => 'El estado es requerido',
            'is_active.boolean' => 'El estado debe ser verdadero o falso'
        ]);

        try {
            $user = User::findOrFail($id);
            
            // Prevent self deactivation
            if ($request->user()->id == $id && !$request->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'No puedes desactivar tu propia cuenta'
                ], 403);
            }

            $user->update(['is_active' => $request->is_active]);
            
            $status = $request->is_active ? 'activado' : 'desactivado';
            
            return response()->json([
                'success' => true,
                'message' => "Usuario {$status} exitosamente",
                'user' => new UserResource($user->fresh())
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar estado del usuario'
            ], 500);
        }
    }
}