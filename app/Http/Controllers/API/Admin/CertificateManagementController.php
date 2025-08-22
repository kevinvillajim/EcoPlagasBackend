<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SendCertificateEmailJob;
use App\Models\Certificate;
use App\Models\Service;
use App\Http\Resources\CertificateResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;

class CertificateManagementController extends Controller
{
    /**
     * Get all certificates with pagination and filtering
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 15);
            $status = $request->get('status');
            $search = $request->get('search');
            $userId = $request->get('user_id');

            $certificates = Certificate::query()
                ->with(['user:id,name,email', 'service:id,type,description', 'issuer:id,name'])
                ->when($status, function ($query, $status) {
                    return $query->where('status', $status);
                })
                ->when($search, function ($query, $search) {
                    return $query->where(function ($q) use ($search) {
                        $q->where('certificate_number', 'like', "%{$search}%")
                          ->orWhere('type', 'like', "%{$search}%")
                          ->orWhereHas('user', function ($userQuery) use ($search) {
                              $userQuery->where('name', 'like', "%{$search}%")
                                       ->orWhere('email', 'like', "%{$search}%");
                          });
                    });
                })
                ->when($userId, function ($query, $userId) {
                    return $query->where('user_id', $userId);
                })
                ->latest('issue_date')
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'certificates' => CertificateResource::collection($certificates->items()),
                'pagination' => [
                    'current_page' => $certificates->currentPage(),
                    'total_pages' => $certificates->lastPage(),
                    'per_page' => $certificates->perPage(),
                    'total' => $certificates->total(),
                    'from' => $certificates->firstItem(),
                    'to' => $certificates->lastItem(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener certificados'
            ], 500);
        }
    }

    /**
     * Create a new certificate for a completed service
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'service_id' => 'required|exists:services,id',
            'type' => 'required|string|max:255',
            'valid_until' => 'required|date|after:today',
            // Client information validation
            'client_name' => 'required|string|max:255',
            'client_ruc' => 'nullable|string|max:20',
            'address' => 'required|string|max:500',
            'city' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:20',
            'treated_area' => 'nullable|string|max:500',
            // Service types validation
            'desinsectacion' => 'boolean',
            'desinfeccion' => 'boolean',
            'desratizacion' => 'boolean',
            'otro_servicio' => 'boolean',
            // Products and categories validation
            'producto_desinsectacion' => 'nullable|string|max:255',
            'categoria_desinsectacion' => 'nullable|string|max:255',
            'registro_desinsectacion' => 'nullable|string|max:255',
            'producto_desinfeccion' => 'nullable|string|max:255',
            'categoria_desinfeccion' => 'nullable|string|max:255',
            'registro_desinfeccion' => 'nullable|string|max:255',
            'producto_desratizacion' => 'nullable|string|max:255',
            'categoria_desratizacion' => 'nullable|string|max:255',
            'registro_desratizacion' => 'nullable|string|max:255',
            'producto_otro' => 'nullable|string|max:255',
            'categoria_otro' => 'nullable|string|max:255',
            'registro_otro' => 'nullable|string|max:255',
            // Additional information validation
            'service_description' => 'nullable|string|max:1000',
        ], [
            'service_id.required' => 'El servicio es requerido',
            'service_id.exists' => 'El servicio seleccionado no existe',
            'type.required' => 'El tipo de certificado es requerido',
            'valid_until.required' => 'La fecha de vencimiento es requerida',
            'valid_until.after' => 'La fecha de vencimiento debe ser posterior a hoy',
            'client_name.required' => 'El nombre del cliente es requerido',
            'address.required' => 'La dirección es requerida',
        ]);

        try {
            // Verify service is completed
            $service = Service::with('user')->findOrFail($request->service_id);
            
            if ($service->status !== Service::STATUS_COMPLETED) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo se pueden generar certificados para servicios completados'
                ], 422);
            }

            // Check if certificate already exists for this service
            if ($service->certificates()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ya existe un certificado para este servicio'
                ], 422);
            }

            // Generate unique certificate number
            $certificateNumber = 'CERT-' . strtoupper(Str::random(8)) . '-' . date('Y');

            // Create certificate record (no file upload needed, PDF will be generated on demand)
            $certificate = Certificate::create([
                'user_id' => $service->user_id,
                'service_id' => $service->id,
                'certificate_number' => $certificateNumber,
                'file_name' => null, // No physical file stored
                'file_path' => null, // No physical file stored
                'issue_date' => now(),
                'valid_until' => $request->valid_until,
                'type' => $request->type,
                'status' => Certificate::STATUS_VALID,
                'issued_by' => $request->user()->id,
                // Client information
                'client_name' => $request->client_name,
                'client_ruc' => $request->client_ruc,
                'address' => $request->address,
                'city' => $request->city ?: 'QUITO',
                'phone' => $request->phone,
                'treated_area' => $request->treated_area,
                // Service types
                'desinsectacion' => $request->boolean('desinsectacion'),
                'desinfeccion' => $request->boolean('desinfeccion'),
                'desratizacion' => $request->boolean('desratizacion'),
                'otro_servicio' => $request->boolean('otro_servicio'),
                // Products and categories
                'producto_desinsectacion' => $request->producto_desinsectacion,
                'categoria_desinsectacion' => $request->categoria_desinsectacion,
                'registro_desinsectacion' => $request->registro_desinsectacion,
                'producto_desinfeccion' => $request->producto_desinfeccion,
                'categoria_desinfeccion' => $request->categoria_desinfeccion,
                'registro_desinfeccion' => $request->registro_desinfeccion,
                'producto_desratizacion' => $request->producto_desratizacion,
                'categoria_desratizacion' => $request->categoria_desratizacion,
                'registro_desratizacion' => $request->registro_desratizacion,
                'producto_otro' => $request->producto_otro,
                'categoria_otro' => $request->categoria_otro,
                'registro_otro' => $request->registro_otro,
                // Additional information
                'service_description' => $request->service_description,
            ]);

            $certificate->load(['user:id,name,email', 'service:id,type,description', 'issuer:id,name']);

            // Enviar certificado por email automáticamente
            try {
                SendCertificateEmailJob::dispatch($certificate);
                Log::info('Certificate email job dispatched', [
                    'certificate_id' => $certificate->id,
                    'user_email' => $certificate->user->email
                ]);
            } catch (\Exception $e) {
                Log::error('Error dispatching certificate email job', [
                    'certificate_id' => $certificate->id,
                    'error' => $e->getMessage()
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Certificado creado exitosamente y enviado por email al cliente.',
                'certificate' => new CertificateResource($certificate)
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear certificado: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get specific certificate
     */
    public function show(Request $request, $id): JsonResponse
    {
        try {
            $certificate = Certificate::with(['user', 'service', 'issuer'])
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'certificate' => new CertificateResource($certificate)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Certificado no encontrado'
            ], 404);
        }
    }

    /**
     * Update certificate status
     */
    public function update(Request $request, $id): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:valid,expired,revoked',
            'valid_until' => 'sometimes|date',
        ]);

        try {
            $certificate = Certificate::findOrFail($id);
            
            $updateData = [
                'status' => $request->status,
            ];

            if ($request->has('valid_until')) {
                $updateData['valid_until'] = $request->valid_until;
            }

            $certificate->update($updateData);
            $certificate->load(['user:id,name,email', 'service:id,type,description', 'issuer:id,name']);

            return response()->json([
                'success' => true,
                'message' => 'Certificado actualizado exitosamente',
                'certificate' => new CertificateResource($certificate)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Certificado no encontrado'
            ], 404);
        }
    }

    /**
     * Delete certificate
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        try {
            $certificate = Certificate::findOrFail($id);
            
            // Delete file from storage
            if (Storage::disk('public')->exists($certificate->file_path)) {
                Storage::disk('public')->delete($certificate->file_path);
            }

            $certificate->delete();

            return response()->json([
                'success' => true,
                'message' => 'Certificado eliminado exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Certificado no encontrado'
            ], 404);
        }
    }

    /**
     * Get services eligible for certification (completed services without certificates)
     */
    public function getEligibleServices(Request $request): JsonResponse
    {
        try {
            $services = Service::with(['user:id,name,email,ruc,phone,address,city'])
                ->where('status', Service::STATUS_COMPLETED)
                ->whereDoesntHave('certificates')
                ->latest('completed_date')
                ->get();

            return response()->json([
                'success' => true,
                'services' => $services->map(function ($service) {
                    return [
                        'id' => $service->id,
                        'type' => $service->type,
                        'description' => $service->description,
                        'address' => $service->address,
                        'completed_date' => $service->completed_date?->format('Y-m-d'),
                        'client' => [
                            'id' => $service->user->id,
                            'name' => $service->user->name,
                            'email' => $service->user->email,
                            'ruc' => $service->user->ruc,
                            'phone' => $service->user->phone,
                            'address' => $service->user->address,
                            'city' => $service->user->city,
                        ]
                    ];
                })
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener servicios elegibles'
            ], 500);
        }
    }

    /**
     * Get certificate statistics
     */
    public function getStats(Request $request): JsonResponse
    {
        try {
            $stats = [
                'total_certificates' => Certificate::count(),
                'valid_certificates' => Certificate::where('status', Certificate::STATUS_VALID)->count(),
                'expired_certificates' => Certificate::where('status', Certificate::STATUS_EXPIRED)->count(),
                'revoked_certificates' => Certificate::where('status', Certificate::STATUS_REVOKED)->count(),
                'expiring_soon' => Certificate::where('status', Certificate::STATUS_VALID)
                    ->where('valid_until', '<=', now()->addDays(30))
                    ->count(),
                'recent_certificates' => Certificate::where('created_at', '>=', now()->subDays(30))->count(),
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
     * Generate PDF certificate
     */
    public function generatePdf($id)
    {
        try {
            set_time_limit(60); // 1 minuto es suficiente
            ini_set('memory_limit', '256M');
            
            Log::info('Starting PDF generation for certificate ID: ' . $id);
            
            $certificate = Certificate::with(['user', 'service', 'issuer'])
                ->findOrFail($id);

            Log::info('Certificate loaded', ['certificate_id' => $certificate->id, 'service_id' => $certificate->service_id]);

            // Get the service for the view
            $service = $certificate->service;
            
            Log::info('Service data for PDF', ['service' => $service ? $service->toArray() : null]);

            // Generate PDF with secure settings
            $pdf = Pdf::loadView('pdf.certificate', compact('certificate', 'service'))
                ->setPaper('a4', 'portrait')
                ->setOptions([
                    'isRemoteEnabled' => false, // Seguridad: Deshabilitado acceso remoto
                    'isHtml5ParserEnabled' => true,
                    'chroot' => resource_path('views'), // Restringido solo a views
                    'defaultFont' => 'Arial',
                    'dpi' => 150,
                    'defaultMediaType' => 'print'
                ]);

            $filename = 'certificado_' . $certificate->certificate_number . '.pdf';
            
            Log::info('PDF generated successfully, downloading: ' . $filename);
            
            return $pdf->download($filename);

        } catch (\Exception $e) {
            Log::error('PDF Generation Error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al generar el certificado: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Preview PDF certificate (for browser view)
     */
    public function previewPdf($id)
    {
        try {
            set_time_limit(60); // 1 minuto es suficiente
            ini_set('memory_limit', '256M');
            
            $certificate = Certificate::with(['user', 'service', 'issuer'])
                ->findOrFail($id);

            // Get the service for the view
            $service = $certificate->service;

            // Generate PDF for browser preview with secure settings
            $pdf = Pdf::loadView('pdf.certificate', compact('certificate', 'service'))
                ->setPaper('a4', 'portrait')
                ->setOptions([
                    'isRemoteEnabled' => false, // Seguridad: Deshabilitado acceso remoto
                    'isHtml5ParserEnabled' => true,
                    'chroot' => resource_path('views'),
                    'defaultFont' => 'Arial'
                ]);

            return $pdf->stream('certificado_' . $certificate->certificate_number . '.pdf');

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al generar el certificado: ' . $e->getMessage()
            ], 500);
        }
    }
}