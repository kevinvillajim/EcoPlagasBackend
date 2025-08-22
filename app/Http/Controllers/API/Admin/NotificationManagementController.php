<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class NotificationManagementController extends Controller
{
    /**
     * Get all notifications with pagination and filtering
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 15);
            $type = $request->get('type');
            $userId = $request->get('user_id');
            $search = $request->get('search');

            $notifications = Notification::query()
                ->with(['user:id,name,email'])
                ->when($type, function ($query, $type) {
                    return $query->where('type', $type);
                })
                ->when($userId, function ($query, $userId) {
                    return $query->where('user_id', $userId);
                })
                ->when($search, function ($query, $search) {
                    return $query->where(function ($q) use ($search) {
                        $q->where('title', 'like', "%{$search}%")
                          ->orWhere('message', 'like', "%{$search}%")
                          ->orWhereHas('user', function ($userQuery) use ($search) {
                              $userQuery->where('name', 'like', "%{$search}%")
                                       ->orWhere('email', 'like', "%{$search}%");
                          });
                    });
                })
                ->latest('created_at')
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'notifications' => $notifications->items(),
                'pagination' => [
                    'current_page' => $notifications->currentPage(),
                    'total_pages' => $notifications->lastPage(),
                    'per_page' => $notifications->perPage(),
                    'total' => $notifications->total(),
                    'from' => $notifications->firstItem(),
                    'to' => $notifications->lastItem(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener notificaciones'
            ], 500);
        }
    }

    /**
     * Create notification for user(s)
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'recipient_type' => 'required|in:single,all,role',
            'user_ids' => 'required_if:recipient_type,single|array',
            'user_ids.*' => 'exists:users,id',
            'role' => 'required_if:recipient_type,role|in:client,admin,technician',
            'type' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'message' => 'required|string|max:1000',
            'data' => 'nullable|array',
        ], [
            'recipient_type.required' => 'El tipo de destinatario es requerido',
            'user_ids.required_if' => 'Los usuarios son requeridos para notificación individual',
            'role.required_if' => 'El rol es requerido para notificación por rol',
            'type.required' => 'El tipo de notificación es requerido',
            'title.required' => 'El título es requerido',
            'message.required' => 'El mensaje es requerido',
        ]);

        try {
            $recipients = [];

            switch ($request->recipient_type) {
                case 'single':
                    $recipients = User::whereIn('id', $request->user_ids)->get();
                    break;
                
                case 'all':
                    $recipients = User::where('status', 'active')->get();
                    break;
                
                case 'role':
                    $recipients = User::where('role', $request->role)
                        ->where('status', 'active')
                        ->get();
                    break;
            }

            $notificationsCreated = 0;
            foreach ($recipients as $recipient) {
                Notification::create([
                    'user_id' => $recipient->id,
                    'type' => $request->type,
                    'title' => $request->title,
                    'message' => $request->message,
                    'data' => $request->data,
                    'sent_at' => now(),
                ]);
                $notificationsCreated++;
            }

            return response()->json([
                'success' => true,
                'message' => "Se crearon {$notificationsCreated} notificaciones exitosamente",
                'notifications_created' => $notificationsCreated
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear notificaciones'
            ], 500);
        }
    }

    /**
     * Get specific notification
     */
    public function show(Request $request, $id): JsonResponse
    {
        try {
            $notification = Notification::with(['user:id,name,email'])
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'notification' => $notification
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Notificación no encontrada'
            ], 404);
        }
    }

    /**
     * Delete notification
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        try {
            $notification = Notification::findOrFail($id);
            $notification->delete();

            return response()->json([
                'success' => true,
                'message' => 'Notificación eliminada exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Notificación no encontrada'
            ], 404);
        }
    }

    /**
     * Get notification statistics
     */
    public function getStats(Request $request): JsonResponse
    {
        try {
            $stats = [
                'total_notifications' => Notification::count(),
                'unread_notifications' => Notification::unread()->count(),
                'read_notifications' => Notification::read()->count(),
                'recent_notifications' => Notification::where('created_at', '>=', now()->subDays(7))->count(),
                'by_type' => Notification::selectRaw('type, COUNT(*) as count')
                    ->groupBy('type')
                    ->pluck('count', 'type'),
                'daily_stats' => Notification::selectRaw('DATE(created_at) as date, COUNT(*) as count')
                    ->where('created_at', '>=', now()->subDays(30))
                    ->groupBy('date')
                    ->orderBy('date')
                    ->get()
            ];

            return response()->json([
                'success' => true,
                'stats' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas'
            ], 500);
        }
    }

    /**
     * Send automated notifications based on triggers
     */
    public function sendAutomatedNotifications(Request $request): JsonResponse
    {
        try {
            $notificationsSent = 0;

            // Check for expiring certificates
            $expiringCertificates = \App\Models\Certificate::where('status', 'valid')
                ->where('valid_until', '<=', now()->addDays(30))
                ->where('valid_until', '>', now())
                ->with('user')
                ->get();

            foreach ($expiringCertificates as $certificate) {
                $existing = Notification::where('user_id', $certificate->user_id)
                    ->where('type', Notification::TYPE_CERTIFICATE_EXPIRING)
                    ->where('data->certificate_id', $certificate->id)
                    ->where('created_at', '>=', now()->subDays(7))
                    ->exists();

                if (!$existing) {
                    Notification::create([
                        'user_id' => $certificate->user_id,
                        'type' => Notification::TYPE_CERTIFICATE_EXPIRING,
                        'title' => 'Certificado próximo a expirar',
                        'message' => "Tu certificado {$certificate->certificate_number} expira el " . 
                                   $certificate->valid_until->format('d/m/Y') . '. Contacta con nosotros para renovarlo.',
                        'data' => [
                            'certificate_id' => $certificate->id,
                            'certificate_number' => $certificate->certificate_number,
                            'valid_until' => $certificate->valid_until->format('Y-m-d')
                        ],
                        'sent_at' => now(),
                    ]);
                    $notificationsSent++;
                }
            }

            // Check for upcoming service reminders
            $upcomingServices = \App\Models\Service::where('status', 'scheduled')
                ->where('scheduled_date', '>=', now()->addDay())
                ->where('scheduled_date', '<=', now()->addDays(2))
                ->with('user')
                ->get();

            foreach ($upcomingServices as $service) {
                $existing = Notification::where('user_id', $service->user_id)
                    ->where('type', Notification::TYPE_SERVICE_REMINDER)
                    ->where('data->service_id', $service->id)
                    ->where('created_at', '>=', now()->subDays(1))
                    ->exists();

                if (!$existing) {
                    Notification::create([
                        'user_id' => $service->user_id,
                        'type' => Notification::TYPE_SERVICE_REMINDER,
                        'title' => 'Recordatorio de servicio',
                        'message' => "Tienes un servicio programado para el " . 
                                   $service->scheduled_date->format('d/m/Y') . ' a las ' . $service->scheduled_time . '.',
                        'data' => [
                            'service_id' => $service->id,
                            'scheduled_date' => $service->scheduled_date->format('Y-m-d'),
                            'scheduled_time' => $service->scheduled_time
                        ],
                        'sent_at' => now(),
                    ]);
                    $notificationsSent++;
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Se enviaron {$notificationsSent} notificaciones automáticas",
                'notifications_sent' => $notificationsSent
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al enviar notificaciones automáticas'
            ], 500);
        }
    }
}