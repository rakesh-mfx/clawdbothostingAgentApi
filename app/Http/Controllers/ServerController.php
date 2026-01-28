<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\AgentConnectionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServerController extends Controller
{
    public function __construct(
        protected AgentConnectionService $agentService
    ) {}

    /**
     * Check agent health for a server.
     */
    public function health(string $serverId): JsonResponse
    {
        $result = $this->agentService->checkHealth($serverId);

        return response()->json($result);
    }
}
