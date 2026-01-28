<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ServerLookupService
{
    protected string $panelUrl;
    protected ?string $panelApiKey;
    protected int $defaultPort;
    protected int $cacheTtl = 300; // 5 minutes

    public function __construct()
    {
        $this->panelUrl = config('agent.panel.url');
        $this->panelApiKey = config('agent.panel.api_key');
        $this->defaultPort = config('agent.default_port', 9999);
    }

    /**
     * Get the agent URL for a server.
     */
    public function getAgentUrl(string $serverId): ?string
    {
        $cacheKey = "server_agent_url_{$serverId}";

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($serverId) {
            // Try to fetch from panel
            $url = $this->fetchFromPanel($serverId);

            if ($url) {
                return $url;
            }

            // Fall back to constructing URL
            return $this->constructAgentUrl($serverId);
        });
    }

    /**
     * Fetch agent URL from the main panel.
     */
    protected function fetchFromPanel(string $serverId): ?string
    {
        if (empty($this->panelApiKey)) {
            return null;
        }

        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'Authorization' => "Bearer {$this->panelApiKey}",
                    'Accept' => 'application/json',
                ])
                ->get("{$this->panelUrl}/api/internal/servers/{$serverId}/agent-url");

            if ($response->successful()) {
                $data = $response->json();
                return $data['agent_url'] ?? null;
            }
        } catch (\Exception $e) {
            Log::warning('Failed to fetch agent URL from panel', [
                'server_id' => $serverId,
                'error' => $e->getMessage(),
            ]);
        }

        return null;
    }

    /**
     * Construct agent URL from server info.
     */
    protected function constructAgentUrl(string $serverId): ?string
    {
        // Try to get server info from panel
        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'Authorization' => "Bearer {$this->panelApiKey}",
                    'Accept' => 'application/json',
                ])
                ->get("{$this->panelUrl}/api/internal/servers/{$serverId}");

            if ($response->successful()) {
                $data = $response->json();
                $server = $data['server'] ?? $data;

                // Check for direct agent URL
                if (!empty($server['agent_url'])) {
                    return $server['agent_url'];
                }

                // Get server IP
                $ip = $server['ip_address'] ?? $server['ip'] ?? null;

                if ($ip) {
                    // Use nip.io pattern for automatic DNS resolution
                    return $this->buildNipIoUrl($ip);
                }
            }
        } catch (\Exception $e) {
            Log::warning('Failed to construct agent URL', [
                'server_id' => $serverId,
                'error' => $e->getMessage(),
            ]);
        }

        return null;
    }

    /**
     * Build nip.io URL from server IP.
     *
     * Example: https://clawdbotagent.65.108.209.146.nip.io
     */
    protected function buildNipIoUrl(string $ip): string
    {
        $prefix = config('agent.nip_io.prefix', 'clawdbotagent');
        $useHttps = config('agent.nip_io.https', true);
        $scheme = $useHttps ? 'https' : 'http';

        return "{$scheme}://{$prefix}.{$ip}.nip.io";
    }

    /**
     * Clear cached URL for a server.
     */
    public function clearCache(string $serverId): void
    {
        Cache::forget("server_agent_url_{$serverId}");
    }

    /**
     * Set agent URL directly (for testing or manual configuration).
     */
    public function setAgentUrl(string $serverId, string $url): void
    {
        Cache::put("server_agent_url_{$serverId}", $url, $this->cacheTtl);
    }
}
