<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\FarmController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware(['auth:sanctum', 'farm'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::get('/farms', [FarmController::class, 'index']);
});
