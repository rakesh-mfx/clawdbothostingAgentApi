<?php

declare(strict_types=1);

namespace App\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Log;

class JwtService
{
    protected string $algorithm;
    protected string $audience;
    protected string $issuer;
    protected int $lifetime;
    protected ?string $privateKey;
    protected ?string $publicKey;

    public function __construct()
    {
        $config = config('agent.jwt');

        $this->algorithm = $config['algorithm'];
        $this->audience = $config['audience'];
        $this->issuer = $config['issuer'];
        $this->lifetime = $config['lifetime'];
        $this->privateKey = $this->decodeKey($config['private_key']);
        $this->publicKey = $this->decodeKey($config['public_key']);
    }

    /**
     * Generate a JWT token for agent authentication.
     */
    public function generateToken(array $claims): string
    {
        $now = time();

        $payload = array_merge([
            'iss' => $this->issuer,
            'aud' => $this->audience,
            'iat' => $now,
            'nbf' => $now,
            'exp' => $now + $this->lifetime,
        ], $claims);

        return JWT::encode($payload, $this->privateKey, $this->algorithm);
    }

    /**
     * Generate a token for a specific server/installation.
     */
    public function generateAgentToken(
        string $serverId,
        string $installationId,
        array $permissions = ['*'],
        ?string $userId = null
    ): string {
        return $this->generateToken([
            'sub' => $userId ?? 'system',
            'server_id' => $serverId,
            'installation_id' => $installationId,
            'permissions' => $permissions,
        ]);
    }

    /**
     * Validate and decode a JWT token.
     */
    public function validateToken(string $token): ?object
    {
        try {
            return JWT::decode($token, new Key($this->publicKey, $this->algorithm));
        } catch (\Exception $e) {
            Log::warning('JWT validation failed', [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Decode a base64 encoded key if necessary.
     */
    protected function decodeKey(?string $key): ?string
    {
        if (empty($key)) {
            return null;
        }

        // Check if it's base64 encoded
        if (preg_match('/^[a-zA-Z0-9+\/=]+$/', $key) && !str_contains($key, '-----')) {
            $decoded = base64_decode($key, true);
            if ($decoded !== false) {
                return $decoded;
            }
        }

        return $key;
    }

    /**
     * Get the public key for JWKS endpoint.
     */
    public function getPublicKeyInfo(): array
    {
        $keyResource = openssl_pkey_get_public($this->publicKey);
        $keyDetails = openssl_pkey_get_details($keyResource);

        return [
            'kty' => 'RSA',
            'alg' => $this->algorithm,
            'use' => 'sig',
            'n' => rtrim(strtr(base64_encode($keyDetails['rsa']['n']), '+/', '-_'), '='),
            'e' => rtrim(strtr(base64_encode($keyDetails['rsa']['e']), '+/', '-_'), '='),
            'kid' => md5($this->publicKey),
        ];
    }
}
