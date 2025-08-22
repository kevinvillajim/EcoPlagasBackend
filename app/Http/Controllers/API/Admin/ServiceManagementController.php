<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\User;
use App\Http\Resources\ServiceResource;
use App\Services\EmailService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class ServiceManagementController extends Controller
{
    /**
     * Get all services with pagination and filtering
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 15);
            $status = $request->get('status');
            $search = $request->get('search');
            $technicianId = $request->get('technician_id');

            $services = Service::query()
                ->with(['user:id,name,email,phone,city,role', 'technician:id,name,email'])
                ->when($status, function ($query, $status) {
                    return $query->where('status', $status);
                })
                ->when($search, function ($query, $search) {
                    return $query->where(function ($q) use ($search) {
                        $q->where('type', 'like', "%{$search}%")
                          ->orWhere('description', 'like', "%{$search}%")
                          ->orWhere('address', 'like', "%{$search}%")
                          ->orWhereHas('user', function ($userQuery) use ($search) {
                              $userQuery->where('name', 'like', "%{$search}%")
                                       ->orWhere('email', 'like', "%{$search}%");
                          });
                    });
                })
                ->when($technicianId, function ($query, $technicianId) {
                    return $query->where('technician_id', $technicianId);
                })
                ->latest('scheduled_date')
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'services' => ServiceResource::collection($services->items()),
                'pagination' => [
                    'current_page' => $services->currentPage(),
                    'total_pages' => $services->lastPage(),
                    'per_page' => $services->perPage(),
                    'total' => $services->total(),
                    'from' => $services->firstItem(),
                    'to' => $services->lastItem(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener servicios'
            ], 500);
        }
    }

    /**
     * Create new service (Admin can create for any client)
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'technician_id' => 'nullable|exists:users,id',
            'type' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
            'address' => 'required|string|max:500',
            'scheduled_date' => 'required|date|after_or_equal:today',
            'scheduled_time' => 'required|date_format:H:i',
            'cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ], [
            'user_id.required' => 'El cliente es requerido',
            'user_id.exists' => 'El cliente seleccionado no existe',
            'technician_id.exists' => 'El técnico seleccionado no existe',
            'type.required' => 'El tipo de servicio es requerido',
            'description.required' => 'La descripción es requerida',
            'address.required' => 'La dirección es requerida',
            'scheduled_date.required' => 'La fecha del servicio es requerida',
            'scheduled_date.after_or_equal' => 'La fecha no puede ser anterior a hoy',
            'scheduled_time.required' => 'La hora del servicio es requerida',
            'cost.min' => 'El costo debe ser mayor a 0',
        ]);

        // Verify selected user is a client
        $client = User::where('id', $request->user_id)->where('role', 'client')->first();
        if (!$client) {
            return response()->json([
                'success' => false,
                'message' => 'El usuario seleccionado debe ser un cliente'
            ], 422);
        }

        // Verify technician if provided
        if ($request->technician_id) {
            $technician = User::where('id', $request->technician_id)->where('role', 'technician')->first();
            if (!$technician) {
                return response()->json([
                    'success' => false,
                    'message' => 'El técnico seleccionado no es válido'
                ], 422);
            }
        }

        // Get service settings for conflict checking (except for emergency services)
        if ($request->type !== 'emergency') {
            $serviceSettings = \App\Models\AdminSetting::get('service_settings', []);
            $bufferMinutes = $serviceSettings['bufferTimeBetweenServices'] ?? 30;
            $serviceDurationMinutes = $serviceSettings['defaultServiceDuration'] ?? 120;
            
            $conflictCheck = $this->checkTimeSlotConflicts(
                $request->scheduled_date, 
                $request->scheduled_time, 
                $serviceDurationMinutes, 
                $bufferMinutes
            );
            
            if ($conflictCheck['hasConflict']) {
                return response()->json([
                    'success' => false,
                    'message' => $conflictCheck['message'],
                    'errors' => [
                        'scheduled_time' => [$conflictCheck['message']]
                    ]
                ], 422);
            }
        }

        try {
            $service = Service::create([
                'user_id' => $request->user_id,
                'technician_id' => $request->technician_id,
                'type' => $request->type,
                'description' => $request->description,
                'address' => $request->address,
                'scheduled_date' => $request->scheduled_date,
                'scheduled_time' => $request->scheduled_time,
                'cost' => $request->cost,
                'notes' => $request->notes,
                'status' => Service::STATUS_SCHEDULED,
            ]);

            $service->load(['user:id,name,email', 'technician:id,name,email']);

            return response()->json([
                'success' => true,
                'message' => 'Servicio creado exitosamente',
                'service' => new ServiceResource($service)
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear servicio'
            ], 500);
        }
    }

    /**
     * Get specific service
     */
    public function show(Request $request, $id): JsonResponse
    {
        try {
            $service = Service::with(['user:id,name,email', 'technician:id,name,email', 'certificates', 'reviews'])
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'service' => new ServiceResource($service)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Servicio no encontrado'
            ], 404);
        }
    }

    /**
     * Update service
     */
    public function update(Request $request, $id): JsonResponse
    {
        $request->validate([
            'technician_id' => 'nullable|exists:users,id',
            'type' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string|max:1000',
            'address' => 'sometimes|required|string|max:500',
            'scheduled_date' => 'sometimes|required|date',
            'scheduled_time' => 'sometimes|required|date_format:H:i',
            'completed_date' => 'sometimes|nullable|date',
            'status' => ['sometimes', 'required', Rule::in([
                Service::STATUS_SCHEDULED, 
                Service::STATUS_IN_PROGRESS, 
                Service::STATUS_COMPLETED, 
                Service::STATUS_CANCELLED
            ])],
            'cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
            'next_service_date' => 'nullable|date|after:today',
        ]);

        // Verify technician if provided
        if ($request->technician_id) {
            $technician = User::where('id', $request->technician_id)->where('role', 'technician')->first();
            if (!$technician) {
                return response()->json([
                    'success' => false,
                    'message' => 'El técnico seleccionado no es válido'
                ], 422);
            }
        }

        try {
            $service = Service::findOrFail($id);
            
            // Store original values for comparison
            $originalDate = $service->scheduled_date;
            $originalTime = $service->scheduled_time;
            
            $updateData = $request->only([
                'technician_id', 'type', 'description', 'address', 
                'scheduled_date', 'scheduled_time', 'completed_date',
                'status', 'cost', 'notes', 'next_service_date'
            ]);

            // Set completed_date if status is being changed to completed
            if ($request->status === Service::STATUS_COMPLETED && !$service->completed_date) {
                $updateData['completed_date'] = now();
            }

            $service->update($updateData);
            $service->load(['user:id,name,email', 'technician:id,name,email']);
            
            // Check if date or time changed and send notification email
            // Normalize both date and time formats to compare accurately
            $originalDateFormatted = $originalDate ? \Carbon\Carbon::parse($originalDate)->format('Y-m-d') : null;
            $newDateFormatted = isset($updateData['scheduled_date']) ? 
                \Carbon\Carbon::parse($updateData['scheduled_date'])->format('Y-m-d') : null;
                
            $originalTimeFormatted = $originalTime ? \Carbon\Carbon::parse($originalTime)->format('H:i') : null;
            $newTimeFormatted = isset($updateData['scheduled_time']) ? 
                \Carbon\Carbon::parse($updateData['scheduled_time'])->format('H:i') : null;
            
            $dateChanged = isset($updateData['scheduled_date']) && $newDateFormatted !== $originalDateFormatted;
            $timeChanged = isset($updateData['scheduled_time']) && $newTimeFormatted !== $originalTimeFormatted;
            
            // Add debug logging
            \Log::info('Service update change detection', [
                'service_id' => $service->id,
                'original_date' => $originalDate,
                'original_date_formatted' => $originalDateFormatted,
                'new_date' => $updateData['scheduled_date'] ?? 'unchanged',
                'new_date_formatted' => $newDateFormatted,
                'original_time' => $originalTime,
                'original_time_formatted' => $originalTimeFormatted,
                'new_time' => $updateData['scheduled_time'] ?? 'unchanged',
                'new_time_formatted' => $newTimeFormatted,
                'date_changed' => $dateChanged,
                'time_changed' => $timeChanged
            ]);
            
            if (($dateChanged || $timeChanged) && $service->user) {
                try {
                    $emailService = app(EmailService::class);
                    $emailResult = $emailService->sendServiceDateChangeEmail(
                        $service->user,
                        $service,
                        $dateChanged ? $originalDate : null,
                        $timeChanged ? $originalTime : null
                    );
                    
                    // Log the email result
                    if ($emailResult['success']) {
                        \Log::info('Date change notification sent successfully', [
                            'service_id' => $service->id,
                            'user_id' => $service->user->id,
                            'method' => $emailResult['method']
                        ]);
                    } else {
                        \Log::warning('Date change notification failed', [
                            'service_id' => $service->id,
                            'user_id' => $service->user->id,
                            'error' => $emailResult['message']
                        ]);
                    }
                } catch (\Exception $emailException) {
                    // Don't fail the service update if email fails
                    \Log::error('Failed to send date change notification', [
                        'service_id' => $service->id,
                        'user_id' => $service->user->id,
                        'error' => $emailException->getMessage()
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Servicio actualizado exitosamente',
                'service' => new ServiceResource($service->fresh())
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Servicio no encontrado'
            ], 404);
        }
    }

    /**
     * Delete service (Admin can hard delete)
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        try {
            $service = Service::findOrFail($id);
            $service->delete(); // This will soft delete due to SoftDeletes trait

            return response()->json([
                'success' => true,
                'message' => 'Servicio eliminado exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Servicio no encontrado'
            ], 404);
        }
    }

    /**
     * Assign technician to service
     */
    public function assignTechnician(Request $request, $id): JsonResponse
    {
        $request->validate([
            'technician_id' => 'required|exists:users,id',
        ], [
            'technician_id.required' => 'El técnico es requerido',
            'technician_id.exists' => 'El técnico seleccionado no existe',
        ]);

        // Verify the user is actually a technician
        $technician = User::where('id', $request->technician_id)->where('role', 'technician')->first();
        if (!$technician) {
            return response()->json([
                'success' => false,
                'message' => 'El usuario seleccionado no es un técnico'
            ], 422);
        }

        try {
            $service = Service::findOrFail($id);
            
            // Can't assign technician to cancelled services
            if ($service->status === Service::STATUS_CANCELLED) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede asignar técnico a un servicio cancelado'
                ], 422);
            }

            $service->update([
                'technician_id' => $request->technician_id,
                'status' => Service::STATUS_SCHEDULED // Reset to scheduled if needed
            ]);

            $service->load(['user:id,name,email', 'technician:id,name,email']);

            return response()->json([
                'success' => true,
                'message' => 'Técnico asignado exitosamente',
                'service' => new ServiceResource($service)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Servicio no encontrado'
            ], 404);
        }
    }

    /**
     * Get scheduled services
     */
    public function scheduled(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 15);
            $technicianId = $request->get('technician_id');

            $services = Service::where('status', Service::STATUS_SCHEDULED)
                ->with(['user:id,name,email', 'technician:id,name,email'])
                ->when($technicianId, function ($query, $technicianId) {
                    return $query->where('technician_id', $technicianId);
                })
                ->orderBy('scheduled_date')
                ->orderBy('scheduled_time')
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'services' => ServiceResource::collection($services->items()),
                'pagination' => [
                    'current_page' => $services->currentPage(),
                    'total_pages' => $services->lastPage(),
                    'per_page' => $services->perPage(),
                    'total' => $services->total(),
                    'from' => $services->firstItem(),
                    'to' => $services->lastItem(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener servicios programados'
            ], 500);
        }
    }

    /**
     * Get calendar events (optimized for calendar view)
     */
    public function getCalendarEvents(Request $request): JsonResponse
    {
        try {
            $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
            $endDate = $request->get('end_date', now()->endOfMonth()->format('Y-m-d'));
            $technicianId = $request->get('technician_id');

            // Get service duration from admin settings
            $serviceSettings = \App\Models\AdminSetting::get('service_settings', []);
            $defaultDurationMinutes = $serviceSettings['defaultServiceDuration'] ?? 120;

            $query = Service::select([
                'id',
                'user_id', 
                'technician_id',
                'type',
                'address',
                'scheduled_date',
                'scheduled_time',
                'status',
                'cost',
                'description'
            ])
            ->with([
                'user:id,name,email,phone',
                'technician:id,name,email'
            ])
            ->whereBetween('scheduled_date', [$startDate, $endDate])
            ->whereIn('status', ['scheduled', 'in_progress']);

            if ($technicianId) {
                $query->where('technician_id', $technicianId);
            }

            $services = $query->orderBy('scheduled_date')
                             ->orderBy('scheduled_time')
                             ->get();

            // Transform for calendar format
            $events = $services->map(function ($service) use ($defaultDurationMinutes) {
                // Skip services with missing required data
                if (!$service->scheduled_date || !$service->scheduled_time) {
                    return null;
                }
                
                // Extract just the date part (YYYY-MM-DD) if it has time
                $dateOnly = substr($service->scheduled_date, 0, 10);
                
                // Ensure scheduled_time is in proper format (HH:MM)
                $scheduledTime = $service->scheduled_time;
                if (strlen($scheduledTime) > 5) {
                    // If it includes seconds, remove them (HH:MM:SS -> HH:MM)
                    $scheduledTime = substr($scheduledTime, 0, 5);
                }
                
                // Build start datetime string with clean date and time
                $startStr = $dateOnly . ' ' . $scheduledTime;
                
                try {
                    // Calculate end time using PHP's DateTime
                    $startTime = new \DateTime($startStr);
                    $endTime = clone $startTime;
                    $endTime->add(new \DateInterval('PT' . $defaultDurationMinutes . 'M'));
                    
                    // Format for calendar
                    $startFormatted = $dateOnly . 'T' . $scheduledTime . ':00';
                    $endFormatted = $endTime->format('Y-m-d\TH:i:s');
                } catch (\Exception $e) {
                    // If date parsing fails, skip this service
                    \Log::warning("Calendar: Invalid date/time for service {$service->id}: {$startStr}");
                    return null;
                }

                return [
                    'id' => $service->id,
                    'title' => $this->getEventTitle($service),
                    'start' => $startFormatted,
                    'end' => $endFormatted,
                    'backgroundColor' => $this->getEventColor($service->status),
                    'borderColor' => $this->getEventColor($service->status),
                    'textColor' => '#ffffff',
                    'extendedProps' => [
                        'service' => new ServiceResource($service),
                        'client_name' => $service->user?->name,
                        'technician_name' => $service->technician?->name,
                        'type' => $service->type,
                        'status' => $service->status,
                        'address' => $service->address,
                        'cost' => $service->cost,
                        'phone' => $service->user?->phone,
                    ]
                ];
            })->filter(); // Remove null values from services with invalid dates

            return response()->json([
                'success' => true,
                'events' => $events,
                'summary' => [
                    'total_events' => $events->count(),
                    'scheduled' => $services->where('status', 'scheduled')->count(),
                    'in_progress' => $services->where('status', 'in_progress')->count(),
                    'date_range' => [
                        'start' => $startDate,
                        'end' => $endDate
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar eventos del calendario'
            ], 500);
        }
    }

    /**
     * Get availability slots for scheduling
     */
    public function getAvailabilitySlots(Request $request): JsonResponse
    {
        try {
            $date = $request->get('date', now()->format('Y-m-d'));
            $technicianId = $request->get('technician_id');

            // Get business hours and service settings from admin configuration
            $businessHours = \App\Models\AdminSetting::get('business_hours', []);
            $serviceSettings = \App\Models\AdminSetting::get('service_settings', []);
            $serviceDurationMinutes = $serviceSettings['defaultServiceDuration'] ?? 120;
            $bufferMinutes = $serviceSettings['bufferTimeBetweenServices'] ?? 30;

            // Get the day of week for the requested date
            $dayOfWeek = \Carbon\Carbon::parse($date)->format('l');
            $dayConfig = $businessHours[strtolower($dayOfWeek)] ?? null;

            if (!$dayConfig || !$dayConfig['isOpen']) {
                return response()->json([
                    'success' => true,
                    'date' => $date,
                    'available_slots' => [],
                    'occupied_slots' => [],
                    'total_available' => 0,
                    'message' => 'El día seleccionado está cerrado'
                ]);
            }

            // Generate time slots based on business hours and service duration
            $startTime = \Carbon\Carbon::parse($date . ' ' . $dayConfig['open']);
            $endTime = \Carbon\Carbon::parse($date . ' ' . $dayConfig['close']);
            $slotDuration = $serviceDurationMinutes + $bufferMinutes;
            
            $workingHours = [];
            $currentTime = $startTime->copy();
            
            while ($currentTime->copy()->addMinutes($serviceDurationMinutes)->lte($endTime)) {
                $workingHours[] = $currentTime->format('H:i');
                $currentTime->addMinutes($slotDuration);
            }

            // Get existing services for the date
            $query = Service::select(['scheduled_time', 'technician_id'])
                          ->where('scheduled_date', $date)
                          ->whereIn('status', ['scheduled', 'in_progress']);

            if ($technicianId) {
                $query->where('technician_id', $technicianId);
            }

            $existingServices = $query->get();

            // Get occupied time slots considering service duration
            $occupiedSlots = [];
            foreach ($existingServices as $service) {
                $serviceStart = \Carbon\Carbon::parse($date . ' ' . $service->scheduled_time);
                $serviceEnd = $serviceStart->copy()->addMinutes($serviceDurationMinutes);
                
                // Mark all slots that overlap with this service as occupied
                foreach ($workingHours as $slot) {
                    $slotTime = \Carbon\Carbon::parse($date . ' ' . $slot);
                    $slotEnd = $slotTime->copy()->addMinutes($serviceDurationMinutes);
                    
                    // Check if there's any overlap
                    if (!($slotEnd->lte($serviceStart) || $slotTime->gte($serviceEnd))) {
                        $occupiedSlots[] = $slot;
                    }
                }
            }
            
            $occupiedSlots = array_unique($occupiedSlots);

            // Calculate available slots
            $availableSlots = array_values(array_diff($workingHours, $occupiedSlots));

            return response()->json([
                'success' => true,
                'date' => $date,
                'available_slots' => $availableSlots,
                'occupied_slots' => $occupiedSlots,
                'total_available' => count($availableSlots),
                'working_hours' => $workingHours
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener disponibilidad'
            ], 500);
        }
    }

    /**
     * Get calendar statistics
     */
    public function getCalendarStats(Request $request): JsonResponse
    {
        try {
            $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
            $endDate = $request->get('end_date', now()->endOfMonth()->format('Y-m-d'));

            $stats = Service::selectRaw('
                COUNT(*) as total_services,
                COUNT(CASE WHEN status = "scheduled" THEN 1 END) as scheduled,
                COUNT(CASE WHEN status = "in_progress" THEN 1 END) as in_progress,
                COUNT(CASE WHEN status = "completed" THEN 1 END) as completed,
                COUNT(CASE WHEN status = "cancelled" THEN 1 END) as cancelled,
                COUNT(DISTINCT technician_id) as active_technicians,
                COUNT(DISTINCT user_id) as active_clients,
                COALESCE(SUM(CASE WHEN status = "completed" THEN cost END), 0) as total_revenue
            ')
            ->whereBetween('scheduled_date', [$startDate, $endDate])
            ->first();

            // Get daily distribution
            $dailyStats = Service::selectRaw('
                scheduled_date,
                COUNT(*) as services_count,
                COUNT(CASE WHEN status = "scheduled" THEN 1 END) as scheduled_count
            ')
            ->whereBetween('scheduled_date', [$startDate, $endDate])
            ->groupBy('scheduled_date')
            ->orderBy('scheduled_date')
            ->get();

            return response()->json([
                'success' => true,
                'period_stats' => $stats,
                'daily_distribution' => $dailyStats,
                'date_range' => [
                    'start' => $startDate,
                    'end' => $endDate
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas del calendario'
            ], 500);
        }
    }

    /**
     * Helper function to get event title for calendar
     */
    private function getEventTitle($service): string
    {
        $clientName = $service->user?->name ?? 'Cliente';
        $type = ucfirst($service->type);
        return "{$type} - {$clientName}";
    }

    /**
     * Helper function to get event color based on status
     */
    private function getEventColor($status): string
    {
        $colors = [
            'scheduled' => '#3B82F6', // Blue
            'in_progress' => '#F59E0B', // Orange
            'completed' => '#10B981', // Green
            'cancelled' => '#EF4444', // Red
        ];

        return $colors[$status] ?? '#6B7280'; // Gray as default
    }

    /**
     * Check for time slot conflicts when scheduling services
     */
    private function checkTimeSlotConflicts(string $date, string $time, int $serviceDurationMinutes, int $bufferMinutes): array
    {
        $requestStart = Carbon::parse($date . ' ' . $time);
        $requestEnd = $requestStart->copy()->addMinutes($serviceDurationMinutes);
        
        // Get all existing services for the same date
        $existingServices = Service::where('scheduled_date', $date)
            ->whereIn('status', [Service::STATUS_SCHEDULED, Service::STATUS_IN_PROGRESS])
            ->get();
        
        foreach ($existingServices as $service) {
            $existingStart = Carbon::parse($date . ' ' . $service->scheduled_time);
            // Use the same configured duration for all services
            $existingEnd = $existingStart->copy()->addMinutes($serviceDurationMinutes);
            
            // Add buffer time to the existing service window
            $existingStartWithBuffer = $existingStart->copy()->subMinutes($bufferMinutes);
            $existingEndWithBuffer = $existingEnd->copy()->addMinutes($bufferMinutes);
            
            // Check for conflicts: new service overlaps with existing service (including buffer)
            $hasOverlap = false;
            
            // Case 1: Request start is within the existing service window (with buffer)
            if ($requestStart >= $existingStartWithBuffer && $requestStart < $existingEndWithBuffer) {
                $hasOverlap = true;
            }
            // Case 2: Request end is within the existing service window (with buffer)
            else if ($requestEnd > $existingStartWithBuffer && $requestEnd <= $existingEndWithBuffer) {
                $hasOverlap = true;
            }
            // Case 3: Request completely encompasses the existing service
            else if ($requestStart <= $existingStartWithBuffer && $requestEnd >= $existingEndWithBuffer) {
                $hasOverlap = true;
            }
            
            if ($hasOverlap) {
                return [
                    'hasConflict' => true,
                    'message' => "Ya hay un servicio marcado para esa hora"
                ];
            }
        }
        
        return [
            'hasConflict' => false,
            'message' => 'Horario disponible'
        ];
    }
}