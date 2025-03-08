<?php

use App\Http\Controllers\API\AuthController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    
    // Admin only routes
    Route::middleware('role:admin')->group(function () {
        Route::get('/admin-data', function () {
            return response()->json(['message' => 'Admin data access granted']);
        });
    });
    
    // Staff and admin routes
    Route::middleware('role:admin,staff')->group(function () {
        Route::get('/staff-data', function () {
            return response()->json(['message' => 'Staff data access granted']);
        });
    });
});
