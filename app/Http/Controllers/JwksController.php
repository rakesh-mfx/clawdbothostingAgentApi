<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\JwtService;
use Illuminate\Http\JsonResponse;

class JwksController extends Controller
{
    public function __construct(
        protected JwtService $jwtService
    ) {}

    /**
     * Return JWKS for agents to verify tokens.
     */
    public function __invoke(): JsonResponse
    {
        $keyInfo = $this->jwtService->getPublicKeyInfo();

        return response()->json([
            'keys' => [$keyInfo],
        ]);
    }
}
