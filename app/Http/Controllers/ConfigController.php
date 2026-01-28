<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\AgentConnectionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConfigController extends Controller
{
    public function __construct(
        protected AgentConnectionService $agentService
    ) {}

    /**
     * Get full configuration.
     */
    public function index(Request $request, string $serverId): JsonResponse
    {
        $installationId = $request->input('installation_id', $serverId);

        $result = $this->agentService->get(
            $serverId,
            $installationId,
            '/api/config',
            [],
            ['config:read'],
            $request->user()?->id
        );

        return response()->json($result);
    }

    /**
     * Update configuration.
     */
    public function update(Request $request, string $serverId): JsonResponse
    {
        $installationId = $request->input('installation_id', $serverId);

        $result = $this->agentService->put(
            $serverId,
            $installationId,
            '/api/config',
            $request->except(['installation_id']),
            ['config:update'],
            $request->user()?->id
        );

        return response()->json($result);
    }

    /**
     * Get a specific config section.
     */
    public function getSection(Request $request, string $serverId, string $section): JsonResponse
    {
        $installationId = $request->input('installation_id', $serverId);

        $result = $this->agentService->get(
            $serverId,
            $installationId,
            "/api/config/{$section}",
            [],
            ['config:read'],
            $request->user()?->id
        );

        return response()->json($result);
    }

    /**
     * Update a specific config section.
     */
    public function updateSection(Request $request, string $serverId, string $section): JsonResponse
    {
        $installationId = $request->input('installation_id', $serverId);

        $result = $this->agentService->put(
            $serverId,
            $installationId,
            "/api/config/{$section}",
            $request->except(['installation_id']),
            ['config:update'],
            $request->user()?->id
        );

        return response()->json($result);
    }
}
