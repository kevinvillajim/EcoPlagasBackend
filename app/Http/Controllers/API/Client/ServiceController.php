<?php

namespace App\Http\Controllers\API\Client;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceSetting;
use App\Models\AdminSetting;
use App\Models\Notification;
use App\Http\Resources\ServiceResource;
use App\Services\EmailService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class ServiceController extends Controller
{
    /**
     * Get client services with pagination and filtering
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 15);
            $status = $request->get('status');

            $services = Service::where('user_id', $request->user()->id)
                ->when($status, function ($query, $status) {
                    return $query->where('status', $status);
                })
                ->with(['technician:id,name,email', 'reviews'])
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
     * Get specific service
     */
    public function show(Request $request, $id): JsonResponse
    {
        try {
            $service = Service::where('user_id', $request->user()->id)
                ->with(['technician:id,name,email', 'certificates', 'reviews'])
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
     * Schedule new service
     */
    public function store(Request $request): JsonResponse
    {
        // Get admin settings for dynamic validation
        $businessHours = AdminSetting::get('business_hours', []);
        $serviceSettings = AdminSetting::get('service_settings', []);
        
        // Ensure we have arrays (in case casting fails)
        if (is_string($businessHours)) {
            $businessHours = json_decode($businessHours, true) ?: [];
        }
        if (is_string($serviceSettings)) {
            $serviceSettings = json_decode($serviceSettings, true) ?: [];
        }
        
        $allowWeekendBooking = $serviceSettings['allowWeekendBooking'] ?? true;
        $enableAdvanceBooking = $serviceSettings['enableAdvanceBooking'] ?? true;
        $minimumAdvanceDays = $serviceSettings['minimumAdvanceDays'] ?? 1;
        
        \Log::info('🔧 Backend Service Settings', [
            'minimumAdvanceDays' => $minimumAdvanceDays,
            'enableAdvanceBooking' => $enableAdvanceBooking,
            'allowWeekendBooking' => $allowWeekendBooking
        ]);
        
        // Calculate minimum booking date based on advance booking settings
        if ($enableAdvanceBooking) {
            // Advance booking enabled: use minimum advance days
            $minDate = now()->addDays($minimumAdvanceDays)->format('Y-m-d');
        } else {
            // Advance booking disabled: allow same day booking
            $minDate = now()->format('Y-m-d');
        }
        
        \Log::info('🗓️ Backend Date Validation', [
            'requested_date' => $request->scheduled_date,
            'min_allowed_date' => $minDate,
            'today' => now()->format('Y-m-d'),
        ]);
        
        $request->validate([
            'type' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
            'address' => 'required|string|max:500',
            'scheduled_date' => 'required|date|after_or_equal:' . $minDate,
            'scheduled_time' => 'required|date_format:H:i',
        ], [
            'type.required' => 'El tipo de servicio es requerido',
            'description.required' => 'La descripción es requerida',
            'address.required' => 'La dirección es requerida',
            'scheduled_date.required' => 'La fecha del servicio es requerida',
            'scheduled_date.after_or_equal' => $enableAdvanceBooking ? "La fecha debe ser con al menos {$minimumAdvanceDays} días de anticipación" : 'La fecha debe ser posterior a hoy',
            'scheduled_time.required' => 'La hora del servicio es requerida',
            'scheduled_time.date_format' => 'El formato de hora debe ser HH:MM',
        ]);
        
        // Additional dynamic validation
        $requestDate = Carbon::parse($request->scheduled_date);
        $requestTime = $request->scheduled_time;
        
        // Validate business hours
        \Log::info('🔍 Debug variable types', [
            'serviceSettings_type' => gettype($serviceSettings),
            'serviceSettings_value' => $serviceSettings,
            'businessHours_type' => gettype($businessHours),
        ]);
        $validationResult = $this->validateBookingTime($requestDate, $requestTime, $businessHours, $serviceSettings);
        if (!$validationResult['valid']) {
            return response()->json([
                'success' => false,
                'message' => $validationResult['message'],
                'errors' => [
                    'scheduled_time' => [$validationResult['message']]
                ]
            ], 422);
        }

        try {
            // Check if the time slot is already occupied (including buffer time)
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
            $service = Service::create([
                'user_id' => $request->user()->id,
                'type' => $request->type,
                'description' => $request->description,
                'address' => $request->address,
                'scheduled_date' => $request->scheduled_date,
                'scheduled_time' => $request->scheduled_time,
                'status' => Service::STATUS_SCHEDULED,
            ]);

            // Create notification for the user
            Notification::create([
                'user_id' => $request->user()->id,
                'title' => 'Servicio programado exitosamente',
                'message' => "Tu servicio de {$request->type} ha sido programado para el {$request->scheduled_date} a las {$request->scheduled_time}. Te enviaremos actualizaciones sobre el estado de tu servicio.",
                'type' => Notification::TYPE_SERVICE_SCHEDULED,
                'data' => [
                    'service_id' => $service->id,
                    'service_type' => $request->type,
                    'scheduled_date' => $request->scheduled_date,
                    'scheduled_time' => $request->scheduled_time,
                    'address' => $request->address
                ]
            ]);

            // Create notification for admin users
            $adminUsers = \App\Models\User::where('role', 'admin')->get();
            foreach ($adminUsers as $admin) {
                Notification::create([
                    'user_id' => $admin->id,
                    'title' => 'Nuevo servicio programado',
                    'message' => "El cliente {$request->user()->name} ha programado un servicio de {$request->type} para el {$request->scheduled_date} a las {$request->scheduled_time} en {$request->address}.",
                    'type' => Notification::TYPE_SERVICE_SCHEDULED,
                    'data' => [
                        'service_id' => $service->id,
                        'client_name' => $request->user()->name,
                        'client_email' => $request->user()->email,
                        'service_type' => $request->type,
                        'scheduled_date' => $request->scheduled_date,
                        'scheduled_time' => $request->scheduled_time,
                        'address' => $request->address,
                        'is_admin_notification' => true
                    ]
                ]);
            }

            // Send confirmation email
            $emailService = app(EmailService::class);
            $emailResult = $emailService->sendServiceConfirmationEmail($request->user(), $service);
            
            \Log::info('Service created and confirmation email sent', [
                'service_id' => $service->id,
                'user_id' => $request->user()->id,
                'email_result' => $emailResult
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Servicio programado exitosamente',
                'service' => new ServiceResource($service),
                'email_confirmation' => $emailResult
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al programar servicio'
            ], 500);
        }
    }

    /**
     * Update service (only if scheduled and belongs to user)
     */
    public function update(Request $request, $id): JsonResponse
    {
        $request->validate([
            'type' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string|max:1000',
            'address' => 'sometimes|required|string|max:500',
            'scheduled_date' => 'sometimes|required|date|after:today',
            'scheduled_time' => 'sometimes|required|date_format:H:i',
        ], [
            'type.required' => 'El tipo de servicio es requerido',
            'description.required' => 'La descripción es requerida',
            'address.required' => 'La dirección es requerida',
            'scheduled_date.after' => 'La fecha debe ser posterior a hoy',
            'scheduled_time.date_format' => 'El formato de hora debe ser HH:MM',
        ]);

        try {
            $service = Service::where('user_id', $request->user()->id)
                ->where('status', Service::STATUS_SCHEDULED)
                ->findOrFail($id);

            $service->update($request->only([
                'type', 'description', 'address', 'scheduled_date', 'scheduled_time'
            ]));

            return response()->json([
                'success' => true,
                'message' => 'Servicio actualizado exitosamente',
                'service' => new ServiceResource($service->fresh())
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Servicio no encontrado o no se puede modificar'
            ], 404);
        }
    }

    /**
     * Cancel service (only if scheduled)
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        try {
            $service = Service::where('user_id', $request->user()->id)
                ->where('status', Service::STATUS_SCHEDULED)
                ->findOrFail($id);

            $service->update(['status' => Service::STATUS_CANCELLED]);

            return response()->json([
                'success' => true,
                'message' => 'Servicio cancelado exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Servicio no encontrado o no se puede cancelar'
            ], 404);
        }
    }

    /**
     * Get service history (completed services)
     */
    public function history(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 15);

            $services = Service::where('user_id', $request->user()->id)
                ->whereIn('status', [Service::STATUS_COMPLETED, Service::STATUS_CANCELLED])
                ->with(['technician:id,name,email', 'reviews'])
                ->latest('completed_date')
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
                'message' => 'Error al obtener historial de servicios'
            ], 500);
        }
    }

    /**
     * Get occupied time slots for a specific date
     */
    public function getOccupiedTimeSlots(Request $request): JsonResponse
    {
        $request->validate([
            'date' => 'required|date|after_or_equal:today',
        ]);

        try {
            $date = $request->get('date');
            
            // Get service settings for duration and buffer time
            $serviceSettings = \App\Models\AdminSetting::get('service_settings', []);
            $serviceDurationMinutes = $serviceSettings['defaultServiceDuration'] ?? 120;
            $bufferMinutes = $serviceSettings['bufferTimeBetweenServices'] ?? 30;
            
            // Get all services for the date
            $services = Service::where('scheduled_date', $date)
                ->whereIn('status', [Service::STATUS_SCHEDULED, Service::STATUS_IN_PROGRESS])
                ->get(['scheduled_time']);
            
            $occupiedSlots = [];
            
            foreach ($services as $service) {
                $serviceStart = Carbon::parse($date . ' ' . $service->scheduled_time);
                $serviceEnd = $serviceStart->copy()->addMinutes($serviceDurationMinutes);
                
                // Add buffer time before and after
                $blockStart = $serviceStart->copy()->subMinutes($bufferMinutes);
                $blockEnd = $serviceEnd->copy()->addMinutes($bufferMinutes);
                
                // Generate all blocked time slots (in 30-minute intervals)
                $current = $blockStart->copy();
                while ($current < $blockEnd) {
                    $timeSlot = $current->format('H:i:s');
                    if (!in_array($timeSlot, $occupiedSlots)) {
                        $occupiedSlots[] = $timeSlot;
                    }
                    $current->addMinutes(30); // 30-minute intervals
                }
            }

            // Sort the occupied slots
            sort($occupiedSlots);

            return response()->json([
                'success' => true,
                'occupied_slots' => $occupiedSlots,
                'date' => $date,
                'debug_info' => [
                    'service_duration_minutes' => $serviceDurationMinutes,
                    'buffer_minutes' => $bufferMinutes,
                    'total_services' => $services->count()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener horarios ocupados'
            ], 500);
        }
    }

    /**
     * Schedule immediate emergency service (no time validation)
     */
    public function storeEmergency(Request $request): JsonResponse
    {
        // Validación condicional
        $rules = [
            'type' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
            'address' => 'required|string|max:500',
            'emergency_type' => 'required|in:immediate,out_of_hours',
        ];

        $messages = [
            'type.required' => 'El tipo de servicio es requerido',
            'description.required' => 'La descripción es requerida para servicios de emergencia',
            'address.required' => 'La dirección es requerida',
            'emergency_type.required' => 'El tipo de emergencia es requerido',
            'emergency_type.in' => 'Tipo de emergencia no válido',
        ];

        // Solo requerir fecha y hora para citas fuera de horario
        if ($request->emergency_type === 'out_of_hours') {
            $rules['scheduled_date'] = 'required|date|after_or_equal:today';
            $rules['scheduled_time'] = 'required|date_format:H:i';
            $messages['scheduled_date.required'] = 'La fecha es requerida para citas fuera de horario';
            $messages['scheduled_date.after_or_equal'] = 'La fecha debe ser hoy o posterior';
            $messages['scheduled_time.required'] = 'La hora es requerida para citas fuera de horario';
            $messages['scheduled_time.date_format'] = 'El formato de hora debe ser HH:MM';
        }

        $request->validate($rules, $messages);

        try {
            \Log::info('Emergency service creation started', [
                'emergency_type' => $request->emergency_type,
                'user_id' => $request->user()->id
            ]);

            // Determinar fecha y hora según tipo de emergencia
            if ($request->emergency_type === 'immediate') {
                // Para servicio inmediato, usar fecha y hora actuales
                $now = now();
                $scheduled_date = $now->format('Y-m-d');
                $scheduled_time = $now->format('H:i');
                $status = Service::STATUS_SCHEDULED; // Usar status existente
                $notes = 'EMERGENCIA INMEDIATA - Atención inmediata solicitada - ' . $request->emergency_type;
            } else {
                // Para citas fuera de horario, usar fecha y hora proporcionadas
                $scheduled_date = $request->scheduled_date;
                $scheduled_time = $request->scheduled_time;
                $status = Service::STATUS_SCHEDULED;
                $notes = 'EMERGENCIA FUERA DE HORARIO - Cita fuera de horario - ' . $request->emergency_type;
            }
            
            \Log::info('Creating emergency service', [
                'scheduled_date' => $scheduled_date,
                'scheduled_time' => $scheduled_time,
                'status' => $status
            ]);

            $service = Service::create([
                'user_id' => $request->user()->id,
                'type' => $request->type,
                'description' => $request->description,
                'address' => $request->address,
                'scheduled_date' => $scheduled_date,
                'scheduled_time' => $scheduled_time,
                'status' => $status,
                'notes' => $notes,
            ]);

            // Create notification for the user
            $title = $request->emergency_type === 'immediate' 
                ? 'Solicitud de emergencia recibida' 
                : 'Servicio de emergencia programado';
            
            $message = $request->emergency_type === 'immediate'
                ? "Tu solicitud de emergencia ha sido recibida. Nuestro equipo se contactará contigo en los próximos 15 minutos para coordinar la atención inmediata."
                : "Tu servicio de emergencia ha sido programado fuera del horario normal. Te contactaremos dentro de las próximas 2 horas para confirmar la cita.";

            Notification::create([
                'user_id' => $request->user()->id,
                'title' => $title,
                'message' => $message,
                'type' => Notification::TYPE_SERVICE_SCHEDULED,
                'data' => [
                    'service_id' => $service->id,
                    'service_type' => $request->type,
                    'emergency_type' => $request->emergency_type,
                    'address' => $request->address
                ]
            ]);

            // Create URGENT notification for admin users
            $adminUsers = \App\Models\User::where('role', 'admin')->get();
            foreach ($adminUsers as $admin) {
                $adminTitle = $request->emergency_type === 'immediate'
                    ? '🚨 EMERGENCIA INMEDIATA - Acción requerida'
                    : '⚠️ Servicio de emergencia fuera de horario';
                    
                $adminMessage = $request->emergency_type === 'immediate'
                    ? "ATENCIÓN INMEDIATA REQUERIDA: El cliente {$request->user()->name} necesita servicio de emergencia AHORA. Contactar inmediatamente al {$request->user()->phone}. Dirección: {$request->address}. Problema: {$request->description}"
                    : "El cliente {$request->user()->name} solicita servicio de emergencia fuera del horario normal. Contactar en las próximas 2 horas para coordinar cita.";

                Notification::create([
                    'user_id' => $admin->id,
                    'title' => $adminTitle,
                    'message' => $adminMessage,
                    'type' => 'emergency',
                    'data' => [
                        'service_id' => $service->id,
                        'client_name' => $request->user()->name,
                        'client_email' => $request->user()->email,
                        'client_phone' => $request->user()->phone,
                        'service_type' => $request->type,
                        'emergency_type' => $request->emergency_type,
                        'address' => $request->address,
                        'description' => $request->description,
                        'is_admin_notification' => true,
                        'is_urgent' => $request->emergency_type === 'immediate'
                    ]
                ]);
            }

            // Send emergency emails
            $emailService = app(EmailService::class);
            $clientEmailResult = ['success' => true, 'method' => 'disabled'];
            $adminEmailResults = [];
            
            try {
                // Email to client
                $clientEmailResult = $emailService->sendEmergencyServiceEmail($request->user(), $service, $request->emergency_type);
                
                // Email to administrators
                foreach ($adminUsers as $admin) {
                    try {
                        $adminEmailResult = $emailService->sendEmergencyAdminNotificationEmail(
                            $admin, 
                            $service, 
                            $request->user(), 
                            $request->emergency_type
                        );
                        $adminEmailResults[] = $adminEmailResult;
                    } catch (\Exception $emailError) {
                        \Log::error('Error sending emergency email to admin', [
                            'admin_id' => $admin->id,
                            'error' => $emailError->getMessage()
                        ]);
                        $adminEmailResults[] = ['success' => false, 'method' => 'error'];
                    }
                }
            } catch (\Exception $emailError) {
                \Log::error('Error sending emergency emails', [
                    'error' => $emailError->getMessage()
                ]);
                $clientEmailResult = ['success' => false, 'method' => 'error'];
            }
            
            \Log::info('Emergency service created with email notifications', [
                'service_id' => $service->id,
                'user_id' => $request->user()->id,
                'emergency_type' => $request->emergency_type,
                'client_email_result' => $clientEmailResult,
                'admin_email_results' => $adminEmailResults
            ]);

            return response()->json([
                'success' => true,
                'message' => $request->emergency_type === 'immediate' 
                    ? 'Solicitud de emergencia recibida. Te contactaremos en 15 minutos.'
                    : 'Servicio de emergencia programado. Te contactaremos en las próximas 2 horas.',
                'service' => new ServiceResource($service),
                'email_confirmation' => $clientEmailResult
            ], 201);
        } catch (\Exception $e) {
            \Log::error('Error creating emergency service', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar la solicitud de emergencia'
            ], 500);
        }
    }

    /**
     * Get available service types
     */
    public function getServiceTypes(): JsonResponse
    {
        try {
            $serviceSettings = ServiceSetting::getActive();
            
            $serviceTypes = $serviceSettings->map(function ($setting) {
                $name = match($setting->service_type) {
                    'residential' => 'Control de Plagas Residencial',
                    'commercial' => 'Control de Plagas Comercial',
                    'industrial' => 'Control de Plagas Industrial',
                    'emergency' => 'Servicio de Emergencia',
                    default => ucfirst($setting->service_type)
                };
                
                $description = $setting->description ?: match($setting->service_type) {
                    'residential' => 'Tratamientos especializados para hogares y apartamentos',
                    'commercial' => 'Soluciones integrales para empresas y locales comerciales',
                    'industrial' => 'Tratamientos especializados para plantas industriales',
                    'emergency' => 'Atención inmediata para infestaciones severas',
                    default => 'Servicio de control de plagas'
                };
                
                $priceRange = '';
                if ($setting->min_price && $setting->max_price) {
                    $priceRange = '$' . number_format($setting->min_price) . ' - $' . number_format($setting->max_price);
                }
                
                return [
                    'id' => $setting->service_type,
                    'name' => $name,
                    'description' => $description,
                    'price_range' => $priceRange,
                    'duration_hours' => $setting->duration_minutes / 60
                ];
            });

            return response()->json([
                'success' => true,
                'service_types' => $serviceTypes
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener tipos de servicio'
            ], 500);
        }
    }

    /**
     * Get service settings for calendar configuration
     */
    public function getServiceSettings(): JsonResponse
    {
        try {
            $settings = ServiceSetting::getActive();
            
            $serviceConfig = $settings->mapWithKeys(function ($setting) {
                return [$setting->service_type => $setting->duration_minutes / 60];
            });

            return response()->json([
                'success' => true,
                'service_durations' => $serviceConfig
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener configuraciones de servicio'
            ], 500);
        }
    }

    /**
     * Validate if booking time is within business hours and allowed days
     */
    private function validateBookingTime(Carbon $date, string $time, array $businessHours, array $serviceSettings): array
    {
        $dayNames = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
        $dayOfWeek = $dayNames[$date->dayOfWeek];
        
        // Check if day is configured in business hours
        if (!isset($businessHours[$dayOfWeek])) {
            return [
                'valid' => false,
                'message' => 'Día no disponible para reservas'
            ];
        }
        
        $dayConfig = $businessHours[$dayOfWeek];
        
        // Check if day is open
        if (!($dayConfig['isOpen'] ?? false)) {
            return [
                'valid' => false,
                'message' => 'El día seleccionado está cerrado'
            ];
        }
        
        // Check weekend booking policy
        $allowWeekendBooking = $serviceSettings['allowWeekendBooking'] ?? true;
        if (!$allowWeekendBooking && ($date->dayOfWeek === 0 || $date->dayOfWeek === 6)) {
            return [
                'valid' => false,
                'message' => 'No se permiten reservas en fines de semana'
            ];
        }
        
        // Check if time is within business hours
        $openTime = $dayConfig['open'] ?? '08:00';
        $closeTime = $dayConfig['close'] ?? '18:00';
        
        $serviceDurationMinutes = $serviceSettings['defaultServiceDuration'] ?? 120;
        $requestDateTime = Carbon::parse($date->format('Y-m-d') . ' ' . $time);
        $serviceEndTime = $requestDateTime->copy()->addMinutes($serviceDurationMinutes);
        $businessEndTime = Carbon::parse($date->format('Y-m-d') . ' ' . $closeTime);
        
        if ($time < $openTime) {
            return [
                'valid' => false,
                'message' => "El horario debe ser después de las {$openTime}"
            ];
        }
        
        if ($serviceEndTime->greaterThan($businessEndTime)) {
            return [
                'valid' => false,
                'message' => "El servicio terminaría después del horario de cierre ({$closeTime})"
            ];
        }
        
        return ['valid' => true, 'message' => ''];
    }

    /**
     * Check for time slot conflicts considering service duration and buffer time
     */
    private function checkTimeSlotConflicts(string $date, string $time, int $serviceDurationMinutes, int $bufferMinutes): array
    {
        $requestStart = Carbon::parse($date . ' ' . $time);
        $requestEnd = $requestStart->copy()->addMinutes($serviceDurationMinutes);
        
        // Add logging for debugging
        \Log::info('🕐 Checking time slot conflicts', [
            'date' => $date,
            'time' => $time,
            'duration_minutes' => $serviceDurationMinutes,
            'buffer_minutes' => $bufferMinutes,
            'request_start' => $requestStart->format('Y-m-d H:i'),
            'request_end' => $requestEnd->format('Y-m-d H:i')
        ]);
        
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
            
            \Log::info('🔍 Checking against existing service', [
                'service_id' => $service->id,
                'existing_start' => $existingStart->format('H:i'),
                'existing_end' => $existingEnd->format('H:i'),
                'with_buffer_start' => $existingStartWithBuffer->format('H:i'),
                'with_buffer_end' => $existingEndWithBuffer->format('H:i')
            ]);
            
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
                $conflictTime = $service->scheduled_time;
                $bufferMinutesDisplay = $bufferMinutes;
                
                \Log::warning('⚠️ Time slot conflict detected', [
                    'conflict_with_service' => $service->id,
                    'conflict_time' => $conflictTime
                ]);
                
                return [
                    'hasConflict' => true,
                    'message' => "Conflicto con servicio programado a las {$conflictTime}. Se requiere {$bufferMinutesDisplay} minutos de separación entre servicios."
                ];
            }
        }
        
        return ['hasConflict' => false, 'message' => ''];
    }
}