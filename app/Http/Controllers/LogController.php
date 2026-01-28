<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\AgentConnectionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LogController extends Controller
{
    public function __construct(
        protected AgentConnectionService $agentService
    ) {}

    /**
     * Get recent logs.
     */
    public function recent(Request $request, string $serverId): JsonResponse
    {
        $installationId = $request->input('installation_id', $serverId);

        $result = $this->agentService->get(
            $serverId,
            $installationId,
            '/api/logs/recent',
            $request->only(['lines', 'level', 'channel']),
            ['logs:read'],
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
            '/api/logs/stream'
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

    /**
     * Download logs.
     */
    public function download(Request $request, string $serverId): JsonResponse
    {
        $installationId = $request->input('installation_id', $serverId);

        // Return download URL that frontend can use
        $streamInfo = $this->agentService->getStreamUrl(
            $serverId,
            $installationId,
            '/api/logs/download'
        );

        if (!$streamInfo) {
            return response()->json([
                'success' => false,
                'error' => 'Unable to generate download URL',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'data' => $streamInfo,
        ]);
    }
}
