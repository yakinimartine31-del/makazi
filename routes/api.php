<?php

use App\Http\Controllers\Api\V2\AuthController;
use App\Http\Controllers\Api\V2\LocationController;
use App\Http\Controllers\Api\V2\RoomController;
use Illuminate\Support\Facades\Route;

Route::prefix('v2')->name('api.v2.')->group(function () {

    // ─── Public Authentication ──────────────────────────────────────────────
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);

    // ─── Protected Authentication ──────────────────────────────────────────
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
    });

    // ─── Public Rooms ──────────────────────────────────────────────────────
    Route::get('/rooms', [RoomController::class, 'index']);
    Route::get('/rooms/{room}', [RoomController::class, 'show']);

    // ─── Protected Room Management (Owner or Admin) ───────────────────────
    Route::middleware(['auth:sanctum', 'role:owner,admin'])->group(function () {
        Route::post('/rooms', [RoomController::class, 'store']);
        Route::put('/rooms/{room}', [RoomController::class, 'update']);
        Route::delete('/rooms/{room}', [RoomController::class, 'destroy']);
    });

    // ─── Public Locations ────────────────────────────────────────────────────
    Route::get('/locations/regions', [LocationController::class, 'regions']);
    Route::get('/locations/districts/{region}', [LocationController::class, 'districts']);
    Route::get('/locations/wards/{district}', [LocationController::class, 'wards']);
    Route::get('/locations/areas/{ward}', [LocationController::class, 'areas']);

});
