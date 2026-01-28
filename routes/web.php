<?php

use App\Http\Controllers\JwksController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// JWKS endpoint at root level (without /api prefix) for Go agent OIDC discovery
Route::get('/.well-known/jwks.json', JwksController::class);
