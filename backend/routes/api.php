<?php

use Illuminate\Support\Facades\Route;

// Health check — not versioned (uptime monitors & Docker).
Route::get('/ping', fn () => response()->json([
    'success' => true,
    'message' => 'API is running',
    'data' => null,
]));

// API v1 — all application routes
Route::prefix('v1')->group(base_path('routes/api_v1.php'));
