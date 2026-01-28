<?php

declare(strict_types=1);

use App\Http\Controllers\ChannelController;
use App\Http\Controllers\ConfigController;
use App\Http\Controllers\GatewayController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\JwksController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\MetricsController;
use App\Http\Controllers\PairingController;
use App\Http\Controllers\ServerController;
use App\Http\Controllers\SystemController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

// Health check
Route::get('/health', HealthController::class);

// JWKS endpoint for agents to verify tokens
Route::get('/.well-known/jwks.json', JwksController::class);

/*
|--------------------------------------------------------------------------
| JWT Protected Routes (for frontend/browser access)
|--------------------------------------------------------------------------
*/

Route::middleware(['jwt.token', 'log.requests'])->group(function () {
    // Server metrics endpoints for frontend
    Route::prefix('servers/{server}')->group(function () {
        Route::prefix('metrics')->group(function () {
            Route::get('/all', [MetricsController::class, 'all']);
            Route::get('/cpu', [MetricsController::class, 'cpu']);
            Route::get('/memory', [MetricsController::class, 'memory']);
            Route::get('/disk', [MetricsController::class, 'disk']);
            Route::get('/network', [MetricsController::class, 'network']);
            Route::get('/db-connections', [MetricsController::class, 'dbConnections']);
        });
    });
});

/*
|--------------------------------------------------------------------------
| Protected Routes (require API key - for server-to-server)
|--------------------------------------------------------------------------
*/

Route::middleware(['api.key', 'log.requests'])->group(function () {
    // Server routes
    Route::prefix('servers/{server}')->group(function () {
        // Server health
        Route::get('/health', [ServerController::class, 'health']);

        // Gateway management
        Route::prefix('gateway')->group(function () {
            Route::get('/status', [GatewayController::class, 'status']);
            Route::post('/start', [GatewayController::class, 'start']);
            Route::post('/stop', [GatewayController::class, 'stop']);
            Route::post('/restart', [GatewayController::class, 'restart']);
            Route::get('/logs', [GatewayController::class, 'logs']);
        });

        // Channel management
        Route::prefix('channels')->group(function () {
            Route::get('/', [ChannelController::class, 'index']);
            Route::post('/{type}/configure', [ChannelController::class, 'configure']);
            Route::post('/{type}/enable', [ChannelController::class, 'enable']);
            Route::post('/{type}/disable', [ChannelController::class, 'disable']);
        });

        // Pairing management
        Route::prefix('pairings')->group(function () {
            Route::get('/', [PairingController::class, 'index']);
            Route::post('/{pairingId}/approve', [PairingController::class, 'approve']);
            Route::post('/{pairingId}/reject', [PairingController::class, 'reject']);
            Route::post('/{pairingId}/revoke', [PairingController::class, 'revoke']);
            Route::post('/bulk/approve', [PairingController::class, 'bulkApprove']);
            Route::post('/bulk/reject', [PairingController::class, 'bulkReject']);
            Route::post('/bulk/revoke', [PairingController::class, 'bulkRevoke']);
        });

        // Configuration management
        Route::prefix('config')->group(function () {
            Route::get('/', [ConfigController::class, 'show']);
            Route::put('/', [ConfigController::class, 'update']);
            Route::get('/agents', [ConfigController::class, 'agents']);
            Route::put('/agents', [ConfigController::class, 'updateAgents']);
            Route::get('/security', [ConfigController::class, 'security']);
            Route::put('/security', [ConfigController::class, 'updateSecurity']);
        });

        // System information
        Route::prefix('system')->group(function () {
            Route::get('/info', [SystemController::class, 'info']);
            Route::get('/cpu', [SystemController::class, 'cpu']);
            Route::get('/memory', [SystemController::class, 'memory']);
            Route::get('/disk', [SystemController::class, 'disk']);
        });

        // Metrics (Prometheus proxy)
        Route::prefix('metrics')->group(function () {
            Route::get('/all', [MetricsController::class, 'all']);
            Route::get('/cpu', [MetricsController::class, 'cpu']);
            Route::get('/memory', [MetricsController::class, 'memory']);
            Route::get('/disk', [MetricsController::class, 'disk']);
            Route::get('/network', [MetricsController::class, 'network']);
            Route::get('/db-connections', [MetricsController::class, 'dbConnections']);
            Route::get('/prometheus', [MetricsController::class, 'prometheusRange']);
            Route::get('/prometheus/query', [MetricsController::class, 'prometheusQuery']);
        });

        // Log management
        Route::prefix('logs')->group(function () {
            Route::get('/recent', [LogController::class, 'recent']);
            Route::get('/stream', [LogController::class, 'streamUrl']);
            Route::get('/download', [LogController::class, 'download']);
        });
    });
});
