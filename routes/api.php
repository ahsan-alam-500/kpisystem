<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Api\PubController;
use App\Http\Controllers\Api\KpiImportController;
use App\Http\Controllers\Api\ShiftComplianceController;
use App\Http\Controllers\Api\CompareController;
use App\Http\Controllers\Api\TimeframeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

//clear all cache
Route::get('cc', function() {
    Artisan::call('cache:clear');
    Artisan::call('optimize');
    return "Cache is cleared";
});

// Auth
Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);
Route::post('refresh', [AuthController::class, 'refresh']);
Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:api');

// Public meta (optional)
Route::get('/health', fn() => response()->json(['ok' => true]));

// Protected
Route::middleware('auth:api')->group(function () {

    // current user
    Route::get('/me', [ProfileController::class, 'me']);

    // pubs
    Route::get('/pubs', [PubController::class, 'index']);                 // list + filters
    Route::post('/pubs', [PubController::class, 'store'])->middleware('role:Admin|Manager');
    Route::get('/pubs/{pub}', [PubController::class, 'show']);
    Route::put('/pubs/{pub}', [PubController::class, 'update'])->middleware('role:Admin|Manager');
    Route::delete('/pubs/{pub}', [PubController::class, 'destroy'])->middleware('role:Admin');

    // KPI import (csv/xlsx)
    // Route::post('/kpi/import', [KpiImportController::class, 'store']);
    Route::post('/kpi/import', function(Request $request){
        return response()->json($request->all());
    });

    // shift compliance (pub-week)
    Route::get('/shift', [ShiftComplianceController::class, 'index']); // query by pub_id, week_id
    Route::post('/shift', [ShiftComplianceController::class, 'upsert']); // create/update checklist
    Route::get('/shift/{id}', [ShiftComplianceController::class, 'show']); // single shift compliance

    // compare two weeks of a pub
    Route::get('/compare', [CompareController::class, 'compare']); // ?pub_id=&week_a=&week_b=

    // time frames helper (periods & weeks)
    Route::get('/periods', [TimeframeController::class, 'periods']);
    Route::get('/periods/{period}/weeks', [TimeframeController::class, 'weeks']);
});
