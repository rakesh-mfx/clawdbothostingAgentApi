<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\AgentConnectionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SystemController extends Controller
{
    public function __construct(
        protected AgentConnectionService $agentService
    ) {}

    /**
     * Get full system info.
     */
    public function info(Request $request, string $serverId): JsonResponse
    {
        $installationId = $request->input('installation_id', $serverId);

        $result = $this->agentService->get(
            $serverId,
            $installationId,
            '/api/system/info',
            [],
            ['system:read'],
            $request->user()?->id
        );

        return response()->json($result);
    }

    /**
     * Get CPU info.
     */
    public function cpu(Request $request, string $serverId): JsonResponse
    {
        $installationId = $request->input('installation_id', $serverId);

        $result = $this->agentService->get(
            $serverId,
            $installationId,
            '/api/system/cpu',
            [],
            ['system:read'],
            $request->user()?->id
        );

        return response()->json($result);
    }

    /**
     * Get memory info.
     */
    public function memory(Request $request, string $serverId): JsonResponse
    {
        $installationId = $request->input('installation_id', $serverId);

        $result = $this->agentService->get(
            $serverId,
            $installationId,
            '/api/system/memory',
            [],
            ['system:read'],
            $request->user()?->id
        );

        return response()->json($result);
    }

    /**
     * Get disk info.
     */
    public function disk(Request $request, string $serverId): JsonResponse
    {
        $installationId = $request->input('installation_id', $serverId);

        $result = $this->agentService->get(
            $serverId,
            $installationId,
            '/api/system/disk',
            [],
            ['system:read'],
            $request->user()?->id
        );

        return response()->json($result);
    }
}
