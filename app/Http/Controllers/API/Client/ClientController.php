<?php

namespace App\Http\Controllers\API\Client;

use App\Http\Controllers\Controller;
use App\Http\Traits\ErrorHandler;
use App\Http\Resources\UserResource;
use App\Models\Service;
use App\Models\Certificate;
use App\Models\Notification;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class ClientController extends Controller
{
    use ErrorHandler;

    /**
     * Get client dashboard data
     */
    public function dashboard(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            // Get client statistics
            $totalServices = Service::where('user_id', $user->id)->count();
            $completedServices = Service::where('user_id', $user->id)
                ->where('status', 'completed')->count();
            $pendingServices = Service::where('user_id', $user->id)
                ->where('status', 'scheduled')->count();
            
            $totalCertificates = Certificate::where('user_id', $user->id)->count();
            $validCertificates = Certificate::where('user_id', $user->id)
                ->where('status', 'valid')
                ->where('valid_until', '>=', now())
                ->count();
            
            $unreadNotifications = Notification::where('user_id', $user->id)
                ->whereNull('read_at')->count();
            
            // Get recent services
            $recentServices = Service::where('user_id', $user->id)
                ->with('technician:id,name')
                ->latest('created_at')
                ->limit(5)
                ->get()
                ->map(function($service) {
                    return [
                        'id' => $service->id,
                        'type' => $service->type,
                        'status' => $service->status,
                        'scheduled_date' => $service->scheduled_date,
                        'technician' => $service->technician->name ?? 'Sin asignar',
                        'address' => $service->address
                    ];
                });

            // Get recent notifications
            $recentNotifications = Notification::where('user_id', $user->id)
                ->latest('created_at')
                ->limit(5)
                ->get()
                ->map(function($notification) {
                    return [
                        'id' => $notification->id,
                        'title' => $notification->title,
                        'message' => $notification->message,
                        'type' => $notification->type,
                        'read_at' => $notification->read_at,
                        'created_at' => $notification->created_at
                    ];
                });

            return $this->successResponse([
                'stats' => [
                    'total_services' => $totalServices,
                    'completed_services' => $completedServices,
                    'pending_services' => $pendingServices,
                    'total_certificates' => $totalCertificates,
                    'valid_certificates' => $validCertificates,
                    'unread_notifications' => $unreadNotifications,
                ],
                'recent_services' => $recentServices,
                'recent_notifications' => $recentNotifications
            ], 'Dashboard cargado exitosamente');

        } catch (\Exception $e) {
            return $this->handleException($e, 'Error al cargar dashboard');
        }
    }

    /**
     * Get client profile
     */
    public function profile(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            return $this->successResponse([
                'user' => new UserResource($user)
            ], 'Perfil cargado exitosamente');
            
        } catch (\Exception $e) {
            return $this->handleException($e, 'Error al cargar perfil');
        }
    }

    /**
     * Update client profile
     */
    public function updateProfile(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'phone' => 'nullable|string|max:20',
                'address' => 'nullable|string|max:500',
            ]);

            if ($validator->fails()) {
                return $this->handleValidationError(
                    $validator->errors()->toArray(),
                    'Los datos proporcionados no son válidos'
                );
            }

            $user = $request->user();
            
            // Update only allowed fields
            $user->update($validator->validated());
            
            return $this->successResponse([
                'user' => new UserResource($user->fresh())
            ], 'Perfil actualizado exitosamente');

        } catch (\Exception $e) {
            return $this->handleException($e, 'Error al actualizar perfil');
        }
    }

    /**
     * Get client service statistics
     */
    public function getServiceStats(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            $stats = Service::where('user_id', $user->id)
                ->selectRaw('
                    status,
                    COUNT(*) as count,
                    AVG(cost) as avg_cost,
                    MAX(scheduled_date) as latest_date
                ')
                ->groupBy('status')
                ->get()
                ->keyBy('status');

            $monthlyStats = Service::where('user_id', $user->id)
                ->where('created_at', '>=', now()->subMonths(12))
                ->selectRaw('
                    YEAR(created_at) as year,
                    MONTH(created_at) as month,
                    COUNT(*) as services_count,
                    SUM(cost) as total_cost
                ')
                ->groupBy('year', 'month')
                ->orderBy('year', 'desc')
                ->orderBy('month', 'desc')
                ->get();

            return $this->successResponse([
                'status_stats' => $stats,
                'monthly_stats' => $monthlyStats
            ], 'Estadísticas cargadas exitosamente');

        } catch (\Exception $e) {
            return $this->handleException($e, 'Error al cargar estadísticas');
        }
    }
}