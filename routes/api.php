<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\KRSController;
use App\Http\Controllers\GradingController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\PDDIKTIFeederController;

/*
|--------------------------------------------------------------------------
| API Routes - RESTful Endpoints for Dynamic AJAX / Fetch Interactions
|--------------------------------------------------------------------------
*/

Route::prefix('krs')->group(function () {
    Route::post('/submit', [KRSController::class, 'submitKRS'])->middleware('auth:sanctum');
});

Route::prefix('grades')->group(function () {
    Route::post('/batch-store', [GradingController::class, 'batchStore'])->middleware('auth:sanctum');
});

Route::prefix('finance')->group(function () {
    Route::post('/va/generate', [BillingController::class, 'generateVA'])->middleware('auth:sanctum');
    Route::post('/webhook/callback', [BillingController::class, 'webhookCallback']); // Open for payment gateway IPN
});

Route::prefix('pddikti')->group(function () {
    Route::post('/sync/akm', [PDDIKTIFeederController::class, 'syncAKM'])->middleware('auth:sanctum');
});
