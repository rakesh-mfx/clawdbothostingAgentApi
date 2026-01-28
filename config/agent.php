<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Agent Connection Settings
    |--------------------------------------------------------------------------
    */
    'timeout' => env('AGENT_TIMEOUT', 30),
    'default_port' => env('AGENT_DEFAULT_PORT', 9999),
    'verify_ssl' => env('AGENT_VERIFY_SSL', false),

    /*
    |--------------------------------------------------------------------------
    | JWT Token Settings
    |--------------------------------------------------------------------------
    */
    'jwt' => [
        'algorithm' => env('JWT_ALGORITHM', 'RS256'),
        'audience' => env('JWT_AUDIENCE', 'clawdbot-agent'),
        'issuer' => env('APP_URL', 'https://agentapi.clawdbot.com'),
        'lifetime' => env('JWT_LIFETIME', 3600), // 1 hour
        'private_key' => env('JWT_PRIVATE_KEY') ?: (
            file_exists(storage_path('keys/private.pem'))
                ? file_get_contents(storage_path('keys/private.pem'))
                : null
        ),
        'public_key' => env('JWT_PUBLIC_KEY') ?: (
            file_exists(storage_path('keys/public.pem'))
                ? file_get_contents(storage_path('keys/public.pem'))
                : null
        ),
    ],

    /*
    |--------------------------------------------------------------------------
    | Panel Integration
    |--------------------------------------------------------------------------
    */
    'panel' => [
        'url' => env('PANEL_URL', env('AGENT_PANEL_URL', 'https://clawdbot.com')),
        'api_key' => env('PANEL_API_KEY', env('AGENT_PANEL_API_KEY')),
    ],

    /*
    |--------------------------------------------------------------------------
    | nip.io Domain Settings
    |--------------------------------------------------------------------------
    |
    | nip.io provides wildcard DNS for any IP address.
    | Example: clawdbotagent.65.108.209.146.nip.io -> 65.108.209.146
    |
    */
    'nip_io' => [
        'prefix' => env('AGENT_NIP_IO_PREFIX', 'clawdbotagent'),
        'https' => env('AGENT_NIP_IO_HTTPS', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    */
    'logging' => [
        'enabled' => env('AGENT_LOGGING_ENABLED', true),
        'channel' => env('AGENT_LOG_CHANNEL', 'agent'),
        'retention_days' => env('AGENT_LOG_RETENTION_DAYS', 7),
    ],
];
