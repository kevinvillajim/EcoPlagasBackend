<?php

namespace App\Http\Controllers\API\Client;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Http\Resources\CertificateResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class CertificateController extends Controller
{
    /**
     * Get client certificates with pagination and filtering
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 15);
            $status = $request->get('status');
            $serviceId = $request->get('service_id');
            
            $certificates = Certificate::where('user_id', $request->user()->id)
                ->with(['service:id,type,description,completed_date', 'issuer:id,name'])
                ->when($status, function ($query, $status) {
                    return $query->where('status', $status);
                })
                ->when($serviceId, function ($query, $serviceId) {
                    return $query->where('service_id', $serviceId);
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
     * Get specific certificate details
     */
    public function show(Request $request, $id): JsonResponse
    {
        try {
            $certificate = Certificate::where('user_id', $request->user()->id)
                ->with(['service', 'issuer:id,name'])
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
     * Download certificate PDF
     */
    public function download(Request $request, $id)
    {
        try {
            set_time_limit(60);
            ini_set('memory_limit', '256M');
            
            $certificate = Certificate::where('user_id', $request->user()->id)
                ->with(['user', 'service', 'issuer'])
                ->findOrFail($id);

            $service = $certificate->service;
            
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.certificate', compact('certificate', 'service'))
                ->setPaper('a4', 'portrait')
                ->setOptions([
                    'isRemoteEnabled' => false, // Seguridad: Deshabilitado acceso remoto
                    'isHtml5ParserEnabled' => true,
                    'chroot' => resource_path('views'), // Restringido solo a views
                    'defaultFont' => 'Arial',
                    'dpi' => 150,
                    'defaultMediaType' => 'print'
                ]);

            $filename = "certificado_ecoplagas_{$certificate->certificate_number}.pdf";

            return $pdf->download($filename);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al descargar certificado'
            ], 500);
        }
    }
}