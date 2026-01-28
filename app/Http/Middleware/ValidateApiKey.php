<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateApiKey
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->header('X-API-Key') ?? $request->bearerToken();

        if (empty($apiKey)) {
            return response()->json([
                'success' => false,
                'error' => 'API key required',
            ], 401);
        }

        // Validate against panel API key
        $validKey = config('agent.panel.api_key');

        if (empty($validKey) || $apiKey !== $validKey) {
            return response()->json([
                'success' => false,
                'error' => 'Invalid API key',
            ], 401);
        }

        return $next($request);
    }
}
