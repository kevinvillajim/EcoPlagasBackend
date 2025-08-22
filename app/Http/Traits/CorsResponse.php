<?php

namespace App\Http\Traits;

use Illuminate\Http\JsonResponse;

trait CorsResponse
{
    /**
     * Create a JSON response with CORS headers
     */
    protected function corsResponse(array $data, int $status = 200): JsonResponse
    {
        $allowedOrigins = [
            'http://localhost:5173',
            'http://localhost:5174', 
            'http://127.0.0.1:5173',
            'http://127.0.0.1:5174',
            'http://localhost:3000',
            'http://127.0.0.1:3000',
        ];

        $origin = request()->headers->get('Origin');
        $allowOrigin = in_array($origin, $allowedOrigins) ? $origin : 'http://localhost:5173';

        return response()->json($data, $status)
            ->header('Access-Control-Allow-Origin', $allowOrigin)
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Accept, Authorization, Content-Type, X-Requested-With, X-CSRF-TOKEN, X-XSRF-TOKEN')
            ->header('Access-Control-Allow-Credentials', 'true');
    }
}