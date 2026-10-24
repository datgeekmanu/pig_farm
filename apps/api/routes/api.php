<?php

use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BatchController;
use App\Http\Controllers\Api\FarmController;
use App\Http\Controllers\Api\GoogleAuthController;
use App\Http\Controllers\Api\PasswordResetController;
use App\Http\Controllers\Api\PigController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/auth/google', [GoogleAuthController::class, 'login']);
Route::post('/forgot-password', [PasswordResetController::class, 'forgot']);
Route::post('/reset-password', [PasswordResetController::class, 'reset']);

Route::middleware(['auth:sanctum', 'farm'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::get('/farms', [FarmController::class, 'index']);

    Route::put('/account/password', [AccountController::class, 'updatePassword']);
    Route::post('/account/reset-farm', [AccountController::class, 'resetFarm'])->middleware('role:owner');
    Route::delete('/account', [AccountController::class, 'destroy'])->middleware('role:owner');

    Route::apiResource('batches', BatchController::class);
    Route::apiResource('pigs', PigController::class);
});
