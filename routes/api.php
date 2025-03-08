<?php

use App\Http\Controllers\API\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\TaxiController;

// Public routes

Route::post('/login', [AuthController::class, 'login']);
Route::get('taxis', [TaxiController::class, 'index']);
Route::get('taxis/{taxi}', [TaxiController::class, 'show']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/logout', [AuthController::class, 'logout']);
    
    
    // Admin only routes
    Route::middleware('role:admin')->group(function () {      
        Route::post('/register', [AuthController::class, 'register']);
        Route::apiResource('taxis', TaxiController::class)->except(['index', 'show']);
        Route::get('/user', [AuthController::class, 'user']);
    });
    
    // Staff and admin routes
    Route::middleware('role:admin,staff')->group(function () {
       

    

    });
});



