<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\AgentConnectionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MetricsController extends Controller
{
    public function __construct(
        protected AgentConnectionService $agentService
    ) {}

    /**
     * Get all metrics (Prometheus) for monitoring dashboard.
     */
    public function all(Request $request, string $serverId): JsonResponse
    {
        $installationId = $request->input('installation_id', $serverId);
        $duration = $request->input('duration', '1h');

        $result = $this->agentService->get(
            $serverId,
            $installationId,
            '/api/metrics/all',
            ['duration' => $duration],
            ['metrics:read'],
            $request->user()?->id
        );

        return response()->json($result);
    }

    /**
     * Get CPU metrics from Prometheus.
     */
    public function cpu(Request $request, string $serverId): JsonResponse
    {
        $installationId = $request->input('installation_id', $serverId);
        $duration = $request->input('duration', '1h');

        $result = $this->agentService->get(
            $serverId,
            $installationId,
            '/api/metrics/cpu',
            ['duration' => $duration],
            ['metrics:read'],
            $request->user()?->id
        );

        return response()->json($result);
    }

    /**
     * Get memory metrics from Prometheus.
     */
    public function memory(Request $request, string $serverId): JsonResponse
    {
        $installationId = $request->input('installation_id', $serverId);
        $duration = $request->input('duration', '1h');

        $result = $this->agentService->get(
            $serverId,
            $installationId,
            '/api/metrics/memory',
            ['duration' => $duration],
            ['metrics:read'],
            $request->user()?->id
        );

        return response()->json($result);
    }

    /**
     * Get disk metrics from Prometheus.
     */
    public function disk(Request $request, string $serverId): JsonResponse
    {
        $installationId = $request->input('installation_id', $serverId);
        $duration = $request->input('duration', '1h');

        $result = $this->agentService->get(
            $serverId,
            $installationId,
            '/api/metrics/disk',
            ['duration' => $duration],
            ['metrics:read'],
            $request->user()?->id
        );

        return response()->json($result);
    }

    /**
     * Get network metrics from Prometheus.
     */
    public function network(Request $request, string $serverId): JsonResponse
    {
        $installationId = $request->input('installation_id', $serverId);
        $duration = $request->input('duration', '1h');

        $result = $this->agentService->get(
            $serverId,
            $installationId,
            '/api/metrics/network',
            ['duration' => $duration],
            ['metrics:read'],
            $request->user()?->id
        );

        return response()->json($result);
    }

    /**
     * Get database connection metrics from Prometheus.
     */
    public function dbConnections(Request $request, string $serverId): JsonResponse
    {
        $installationId = $request->input('installation_id', $serverId);
        $duration = $request->input('duration', '1h');

        $result = $this->agentService->get(
            $serverId,
            $installationId,
            '/api/metrics/db-connections',
            ['duration' => $duration],
            ['metrics:read'],
            $request->user()?->id
        );

        return response()->json($result);
    }

    /**
     * Proxy Prometheus range query.
     */
    public function prometheusRange(Request $request, string $serverId): JsonResponse
    {
        $installationId = $request->input('installation_id', $serverId);

        $result = $this->agentService->get(
            $serverId,
            $installationId,
            '/api/metrics/prometheus',
            $request->only(['query', 'start', 'end', 'step']),
            ['metrics:read'],
            $request->user()?->id
        );

        return response()->json($result);
    }

    /**
     * Proxy Prometheus instant query.
     */
    public function prometheusQuery(Request $request, string $serverId): JsonResponse
    {
        $installationId = $request->input('installation_id', $serverId);

        $result = $this->agentService->get(
            $serverId,
            $installationId,
            '/api/metrics/prometheus/query',
            $request->only(['query', 'time']),
            ['metrics:read'],
            $request->user()?->id
        );

        return response()->json($result);
    }
}
