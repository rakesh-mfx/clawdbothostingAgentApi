<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\AgentConnectionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GatewayController extends Controller
{
    public function __construct(
        protected AgentConnectionService $agentService
    ) {}

    /**
     * Get gateway status.
     */
    public function status(Request $request, string $serverId): JsonResponse
    {
        $installationId = $request->input('installation_id', $serverId);

        $result = $this->agentService->get(
            $serverId,
            $installationId,
            '/api/gateway/status',
            [],
            ['gateway:status'],
            $request->user()?->id
        );

        return response()->json($result);
    }

    /**
     * Start gateway.
     */
    public function start(Request $request, string $serverId): JsonResponse
    {
        $installationId = $request->input('installation_id', $serverId);

        $result = $this->agentService->post(
            $serverId,
            $installationId,
            '/api/gateway/start',
            [],
            ['gateway:start'],
            $request->user()?->id
        );

        return response()->json($result);
    }

    /**
     * Stop gateway.
     */
    public function stop(Request $request, string $serverId): JsonResponse
    {
        $installationId = $request->input('installation_id', $serverId);

        $result = $this->agentService->post(
            $serverId,
            $installationId,
            '/api/gateway/stop',
            [],
            ['gateway:stop'],
            $request->user()?->id
        );

        return response()->json($result);
    }

    /**
     * Restart gateway.
     */
    public function restart(Request $request, string $serverId): JsonResponse
    {
        $installationId = $request->input('installation_id', $serverId);

        $result = $this->agentService->post(
            $serverId,
            $installationId,
            '/api/gateway/restart',
            [],
            ['gateway:restart'],
            $request->user()?->id
        );

        return response()->json($result);
    }

    /**
     * Get gateway logs.
     */
    public function logs(Request $request, string $serverId): JsonResponse
    {
        $installationId = $request->input('installation_id', $serverId);
        $lines = $request->input('lines', 100);

        $result = $this->agentService->get(
            $serverId,
            $installationId,
            '/api/gateway/logs',
            ['lines' => $lines],
            ['gateway:logs'],
            $request->user()?->id
        );

        return response()->json($result);
    }

    /**
     * Get stream URL for real-time logs.
     */
    public function streamUrl(Request $request, string $serverId): JsonResponse
    {
        $installationId = $request->input('installation_id', $serverId);

        $streamInfo = $this->agentService->getStreamUrl(
            $serverId,
            $installationId,
            '/api/gateway/logs/stream'
        );

        if (!$streamInfo) {
            return response()->json([
                'success' => false,
                'error' => 'Unable to generate stream URL',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'data' => $streamInfo,
        ]);
    }
}
