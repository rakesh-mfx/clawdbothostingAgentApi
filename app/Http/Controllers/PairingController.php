<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\AgentConnectionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PairingController extends Controller
{
    public function __construct(
        protected AgentConnectionService $agentService
    ) {}

    /**
     * List all pairings.
     */
    public function index(Request $request, string $serverId): JsonResponse
    {
        $installationId = $request->input('installation_id', $serverId);

        $result = $this->agentService->get(
            $serverId,
            $installationId,
            '/api/pairings',
            $request->only(['channel', 'status']),
            ['pairings:list'],
            $request->user()?->id
        );

        return response()->json($result);
    }

    /**
     * Get a specific pairing.
     */
    public function show(Request $request, string $serverId, string $pairingId): JsonResponse
    {
        $installationId = $request->input('installation_id', $serverId);

        $result = $this->agentService->get(
            $serverId,
            $installationId,
            "/api/pairings/{$pairingId}",
            [],
            ['pairings:view'],
            $request->user()?->id
        );

        return response()->json($result);
    }

    /**
     * Approve a pairing.
     */
    public function approve(Request $request, string $serverId, string $pairingId): JsonResponse
    {
        $installationId = $request->input('installation_id', $serverId);

        $result = $this->agentService->post(
            $serverId,
            $installationId,
            "/api/pairings/{$pairingId}/approve",
            [],
            ['pairings:approve'],
            $request->user()?->id
        );

        return response()->json($result);
    }

    /**
     * Reject a pairing.
     */
    public function reject(Request $request, string $serverId, string $pairingId): JsonResponse
    {
        $installationId = $request->input('installation_id', $serverId);

        $result = $this->agentService->post(
            $serverId,
            $installationId,
            "/api/pairings/{$pairingId}/reject",
            $request->only(['reason']),
            ['pairings:reject'],
            $request->user()?->id
        );

        return response()->json($result);
    }

    /**
     * Revoke a pairing.
     */
    public function revoke(Request $request, string $serverId, string $pairingId): JsonResponse
    {
        $installationId = $request->input('installation_id', $serverId);

        $result = $this->agentService->post(
            $serverId,
            $installationId,
            "/api/pairings/{$pairingId}/revoke",
            [],
            ['pairings:revoke'],
            $request->user()?->id
        );

        return response()->json($result);
    }

    /**
     * Bulk approve pairings.
     */
    public function bulkApprove(Request $request, string $serverId): JsonResponse
    {
        $installationId = $request->input('installation_id', $serverId);

        $result = $this->agentService->post(
            $serverId,
            $installationId,
            '/api/pairings/bulk-approve',
            ['ids' => $request->input('ids', [])],
            ['pairings:approve'],
            $request->user()?->id
        );

        return response()->json($result);
    }

    /**
     * Bulk reject pairings.
     */
    public function bulkReject(Request $request, string $serverId): JsonResponse
    {
        $installationId = $request->input('installation_id', $serverId);

        $result = $this->agentService->post(
            $serverId,
            $installationId,
            '/api/pairings/bulk-reject',
            [
                'ids' => $request->input('ids', []),
                'reason' => $request->input('reason'),
            ],
            ['pairings:reject'],
            $request->user()?->id
        );

        return response()->json($result);
    }
}
