<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AgentConnectionService
{
    protected JwtService $jwtService;
    protected ServerLookupService $serverLookup;
    protected int $timeout;
    protected bool $verifySSL;

    public function __construct(JwtService $jwtService, ServerLookupService $serverLookup)
    {
        $this->jwtService = $jwtService;
        $this->serverLookup = $serverLookup;
        $this->timeout = config('agent.timeout', 30);
        $this->verifySSL = config('agent.verify_ssl', false);
    }

    /**
     * Make an authenticated request to an agent.
     */
    public function request(
        string $serverId,
        string $installationId,
        string $method,
        string $endpoint,
        array $data = [],
        array $permissions = ['*'],
        ?string $userId = null
    ): array {
        $requestId = Str::uuid()->toString();
        $startTime = microtime(true);

        $agentUrl = $this->serverLookup->getAgentUrl($serverId);

        if (!$agentUrl) {
            return [
                'success' => false,
                'error' => 'Unable to resolve agent URL for server',
                'request_id' => $requestId,
            ];
        }

        $token = $this->jwtService->generateAgentToken(
            $serverId,
            $installationId,
            $permissions,
            $userId
        );

        $url = rtrim($agentUrl, '/') . '/' . ltrim($endpoint, '/');

        try {
            $this->logRequest($requestId, $method, $url, $data);

            $response = Http::timeout($this->timeout)
                ->withOptions(['verify' => $this->verifySSL])
                ->withHeaders([
                    'Authorization' => "Bearer {$token}",
                    'Accept' => 'application/json',
                    'X-Request-ID' => $requestId,
                ])
                ->$method($url, $data);

            $duration = (microtime(true) - $startTime) * 1000;
            $this->logResponse($requestId, $response, $duration);

            if ($response->successful()) {
                return array_merge(
                    $response->json() ?? [],
                    ['request_id' => $requestId]
                );
            }

            return [
                'success' => false,
                'error' => $response->json('error') ?? 'Request failed',
                'status_code' => $response->status(),
                'request_id' => $requestId,
            ];
        } catch (\Exception $e) {
            $duration = (microtime(true) - $startTime) * 1000;
            $this->logError($requestId, $e, $duration);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'request_id' => $requestId,
            ];
        }
    }

    /**
     * GET request to agent.
     */
    public function get(
        string $serverId,
        string $installationId,
        string $endpoint,
        array $query = [],
        array $permissions = ['*'],
        ?string $userId = null
    ): array {
        $url = $endpoint;
        if (!empty($query)) {
            $url .= '?' . http_build_query($query);
        }

        return $this->request($serverId, $installationId, 'get', $url, [], $permissions, $userId);
    }

    /**
     * POST request to agent.
     */
    public function post(
        string $serverId,
        string $installationId,
        string $endpoint,
        array $data = [],
        array $permissions = ['*'],
        ?string $userId = null
    ): array {
        return $this->request($serverId, $installationId, 'post', $endpoint, $data, $permissions, $userId);
    }

    /**
     * PUT request to agent.
     */
    public function put(
        string $serverId,
        string $installationId,
        string $endpoint,
        array $data = [],
        array $permissions = ['*'],
        ?string $userId = null
    ): array {
        return $this->request($serverId, $installationId, 'put', $endpoint, $data, $permissions, $userId);
    }

    /**
     * DELETE request to agent.
     */
    public function delete(
        string $serverId,
        string $installationId,
        string $endpoint,
        array $permissions = ['*'],
        ?string $userId = null
    ): array {
        return $this->request($serverId, $installationId, 'delete', $endpoint, [], $permissions, $userId);
    }

    /**
     * Check agent health.
     */
    public function checkHealth(string $serverId): array
    {
        $agentUrl = $this->serverLookup->getAgentUrl($serverId);

        if (!$agentUrl) {
            return [
                'success' => false,
                'healthy' => false,
                'error' => 'Unable to resolve agent URL',
            ];
        }

        try {
            $response = Http::timeout(5)
                ->withOptions(['verify' => $this->verifySSL])
                ->get("{$agentUrl}/health");

            return [
                'success' => true,
                'healthy' => $response->successful(),
                'data' => $response->json(),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'healthy' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get SSE stream URL for logs.
     */
    public function getStreamUrl(string $serverId, string $installationId, string $endpoint): ?array
    {
        $agentUrl = $this->serverLookup->getAgentUrl($serverId);

        if (!$agentUrl) {
            return null;
        }

        $token = $this->jwtService->generateAgentToken($serverId, $installationId);
        $url = rtrim($agentUrl, '/') . '/' . ltrim($endpoint, '/');

        return [
            'url' => $url,
            'token' => $token,
            'headers' => [
                'Authorization' => "Bearer {$token}",
            ],
        ];
    }

    /**
     * Log outgoing request.
     */
    protected function logRequest(string $requestId, string $method, string $url, array $data): void
    {
        if (!config('agent.logging.enabled', true)) {
            return;
        }

        Log::channel(config('agent.logging.channel', 'agent'))->info('Agent request', [
            'request_id' => $requestId,
            'method' => strtoupper($method),
            'url' => $url,
            'data' => $this->truncateData($data),
        ]);
    }

    /**
     * Log response.
     */
    protected function logResponse(string $requestId, Response $response, float $duration): void
    {
        if (!config('agent.logging.enabled', true)) {
            return;
        }

        Log::channel(config('agent.logging.channel', 'agent'))->info('Agent response', [
            'request_id' => $requestId,
            'status' => $response->status(),
            'duration_ms' => round($duration, 2),
            'body' => $this->truncateData($response->json() ?? []),
        ]);
    }

    /**
     * Log error.
     */
    protected function logError(string $requestId, \Exception $e, float $duration): void
    {
        Log::channel(config('agent.logging.channel', 'agent'))->error('Agent request failed', [
            'request_id' => $requestId,
            'duration_ms' => round($duration, 2),
            'error' => $e->getMessage(),
            'trace' => array_slice($e->getTrace(), 0, 5),
        ]);
    }

    /**
     * Truncate data for logging.
     */
    protected function truncateData(array $data, int $maxLength = 2048): array
    {
        $json = json_encode($data);

        if (strlen($json) <= $maxLength) {
            return $data;
        }

        return ['_truncated' => true, '_length' => strlen($json)];
    }
}
