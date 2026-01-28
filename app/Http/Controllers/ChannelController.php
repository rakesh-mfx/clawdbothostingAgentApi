<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\AgentConnectionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChannelController extends Controller
{
    public function __construct(
        protected AgentConnectionService $agentService
    ) {}

    /**
     * List all channels.
     */
    public function index(Request $request, string $serverId): JsonResponse
    {
        $installationId = $request->input('installation_id', $serverId);

        $result = $this->agentService->get(
            $serverId,
            $installationId,
            '/api/channels',
            [],
            ['channels:list'],
            $request->user()?->id
        );

        return response()->json($result);
    }

    /**
     * Get channel status.
     */
    public function status(Request $request, string $serverId, string $channelType): JsonResponse
    {
        $installationId = $request->input('installation_id', $serverId);

        $result = $this->agentService->get(
            $serverId,
            $installationId,
            "/api/channels/{$channelType}/status",
            [],
            ['channels:status'],
            $request->user()?->id
        );

        return response()->json($result);
    }

    /**
     * Configure a channel.
     */
    public function configure(Request $request, string $serverId, string $channelType): JsonResponse
    {
        $installationId = $request->input('installation_id', $serverId);

        $result = $this->agentService->post(
            $serverId,
            $installationId,
            "/api/channels/{$channelType}/configure",
            $request->except(['installation_id']),
            ['channels:configure'],
            $request->user()?->id
        );

        return response()->json($result);
    }

    /**
     * Enable a channel.
     */
    public function enable(Request $request, string $serverId, string $channelType): JsonResponse
    {
        $installationId = $request->input('installation_id', $serverId);

        $result = $this->agentService->post(
            $serverId,
            $installationId,
            "/api/channels/{$channelType}/enable",
            [],
            ['channels:configure'],
            $request->user()?->id
        );

        return response()->json($result);
    }

    /**
     * Disable a channel.
     */
    public function disable(Request $request, string $serverId, string $channelType): JsonResponse
    {
        $installationId = $request->input('installation_id', $serverId);

        $result = $this->agentService->post(
            $serverId,
            $installationId,
            "/api/channels/{$channelType}/disable",
            [],
            ['channels:configure'],
            $request->user()?->id
        );

        return response()->json($result);
    }
}
