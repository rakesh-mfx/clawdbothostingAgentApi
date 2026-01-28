<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\JwtService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ValidateJwtToken
{
    public function __construct(
        protected JwtService $jwtService
    ) {}

    /**
     * Handle an incoming request.
     * Validates JWT Bearer token from the main panel app.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (empty($token)) {
            return response()->json([
                'success' => false,
                'error' => 'Authorization token required',
            ], 401);
        }

        // Validate the JWT token
        $payload = $this->jwtService->validateToken($token);

        if (! $payload) {
            return response()->json([
                'success' => false,
                'error' => 'Invalid or expired token',
            ], 401);
        }

        // Check if token is for the correct server
        $serverId = $request->route('server');
        if ($serverId && isset($payload->server_id) && $payload->server_id !== $serverId) {
            Log::warning('JWT token server mismatch', [
                'token_server' => $payload->server_id,
                'request_server' => $serverId,
            ]);
            return response()->json([
                'success' => false,
                'error' => 'Token not valid for this server',
            ], 403);
        }

        // Check permissions if specified
        $requiredPermission = $request->route()->getAction('permission');
        if ($requiredPermission && isset($payload->scope)) {
            $scopes = explode(' ', $payload->scope);
            if (! in_array($requiredPermission, $scopes) && ! in_array('*', $scopes)) {
                return response()->json([
                    'success' => false,
                    'error' => 'Insufficient permissions',
                ], 403);
            }
        }

        // Store the payload for later use
        $request->attributes->set('jwt_payload', $payload);

        return $next($request);
    }
}
