<?php

use App\Http\Controllers\Api\V1\Auth\AuthController;
use Illuminate\Support\Facades\Route;

// All routes are prefixed with /api/v1 (see routes/api.php).
// Groups: public | auth:api (JWT) | auth:api + role:admin

// Auth — public
Route::prefix('auth')->name('auth.')->group(function () {
    Route::post('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/google', [AuthController::class, 'google'])->name('google');

    // Uses the HttpOnly cookie, no Authorization header. Throttled against brute force.
    Route::post('/refresh', [AuthController::class, 'refresh'])
        ->middleware('throttle:10,1')
        ->name('refresh');
});

// Auth — protected
Route::prefix('auth')->name('auth.')->middleware('auth:api')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/me', [AuthController::class, 'me'])->name('me');
});
