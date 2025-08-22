<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateApiParameters
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Validate and sanitize common parameters
        $this->validatePaginationParams($request);
        $this->sanitizeSearchParams($request);
        
        return $next($request);
    }

    /**
     * Validate pagination parameters
     */
    private function validatePaginationParams(Request $request): void
    {
        $perPage = $request->get('per_page');
        
        if ($perPage !== null) {
            $perPage = (int) $perPage;
            
            // Enforce reasonable limits
            if ($perPage < 1) {
                $request->merge(['per_page' => 15]); // Default
            } elseif ($perPage > 100) {
                $request->merge(['per_page' => 100]); // Max limit
            } else {
                $request->merge(['per_page' => $perPage]);
            }
        }
    }

    /**
     * Sanitize search parameters
     */
    private function sanitizeSearchParams(Request $request): void
    {
        $searchFields = ['search', 'query', 'filter', 'name', 'email'];
        
        foreach ($searchFields as $field) {
            if ($request->has($field)) {
                $value = $request->get($field);
                
                // Basic sanitization
                $sanitized = strip_tags(trim($value));
                
                // Remove potential SQL injection patterns
                $sanitized = preg_replace('/[\'";]/', '', $sanitized);
                
                // Limit length
                $sanitized = substr($sanitized, 0, 255);
                
                $request->merge([$field => $sanitized]);
            }
        }
    }
}