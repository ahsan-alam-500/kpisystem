<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\ExcelImportApiController;

Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);

Route::middleware('auth:api')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);

    Route::get('/users', [AuthController::class, 'users']);

    // Protected Excel imports
    Route::post('/import/kpi', [ExcelImportApiController::class, 'importKpi']);
    Route::post('/import/compliance', [ExcelImportApiController::class, 'importCompliance']);
});
