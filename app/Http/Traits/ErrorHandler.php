<?php

namespace App\Http\Traits;

use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;

trait ErrorHandler
{
    /**
     * Handle exceptions safely without exposing sensitive information
     */
    protected function handleException(\Exception $exception, string $userMessage = 'Ha ocurrido un error', int $statusCode = 500): JsonResponse
    {
        // Log the full error for debugging
        Log::error($userMessage . ' - Exception details:', [
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => config('app.debug') ? $exception->getTraceAsString() : 'Trace hidden in production',
            'user_id' => auth()->id(),
            'request_uri' => request()->getUri(),
            'request_method' => request()->method(),
        ]);

        // Return sanitized response to user
        $response = [
            'success' => false,
            'message' => $userMessage
        ];

        // Only add debug information in development environment
        if (config('app.debug') && app()->environment(['local', 'development'])) {
            $response['debug'] = [
                'error' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ];
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Handle validation errors consistently
     */
    protected function handleValidationError(array $errors, string $message = 'Los datos proporcionados no son válidos'): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors
        ], 422);
    }

    /**
     * Handle not found errors
     */
    protected function handleNotFound(string $resource = 'recurso'): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => "El {$resource} solicitado no fue encontrado"
        ], 404);
    }

    /**
     * Handle unauthorized access
     */
    protected function handleUnauthorized(string $message = 'No tienes permisos para realizar esta acción'): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message
        ], 403);
    }

    /**
     * Success response helper
     */
    protected function successResponse($data = null, string $message = 'Operación exitosa', int $statusCode = 200): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $statusCode);
    }
}