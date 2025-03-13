<?php

/**
 * @OA\Info(
 *     title="Island Tours API",
 *     version="1.0.0",
 *     description="API documentation for Island Tours system",
 *     @OA\Contact(
 *         email="admin@example.com"
 *     )
 * )
 */

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\PlanTourController;
use App\Http\Controllers\API\RequestTaxiController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\TaxiController;
use App\Http\Controllers\API\TourController;
use App\Http\Controllers\API\ReviewController;



// Public routes

Route::post('/login', [AuthController::class, 'login']);
Route::get('taxis', [TaxiController::class, 'index']);
Route::get('taxis/{taxi}', [TaxiController::class, 'show']);
Route::get('tours', [TourController::class, 'index']);
Route::get('tours/{tour}', [TourController::class, 'show']);
Route::get('reviews', [ReviewController::class, 'index']);
Route::post('reviews', [ReviewController::class, 'store']);
Route::get('reviews/featured/list', [ReviewController::class, 'featured']);
Route::post('taxi-requests', [RequestTaxiController::class, 'store']);
Route::post('tour-plans', [PlanTourController::class, 'store']);

Route::get('create-storage-link', function () {
    Artisan::call('storage:link');
    return response()->json(['message' => 'Storage link created successfully']);
});

Route::get('clear-cache', function () {
    Artisan::call('cache:clear');
    Artisan::call('config:clear');
    Artisan::call('view:clear');
    return response()->json(['message' => 'All caches cleared successfully']);
});



// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/logout', [AuthController::class, 'logout']);
    Route::get('/verify', [AuthController::class, 'verify']);

    // Admin only routes
    Route::middleware('role:admin')->group(function () {

        Route::post('/register', [AuthController::class, 'register']);
        Route::apiResource('taxis', TaxiController::class)->except(['index', 'show']);
        Route::put('taxis/{id}/status', [TaxiController::class, 'updateStatus']);
        Route::get('/user', [AuthController::class, 'user']);
        Route::apiResource('tours', TourController::class)->except(['index', 'show']);
        Route::put('tours/{id}/status', [TourController::class, 'updateStatus']);
        Route::apiResource('reviews', ReviewController::class)->except(['index', 'store']);
        Route::put('reviews/{id}/status', [ReviewController::class, 'updateStatus']);
        Route::get('reviews/all', [ReviewController::class, 'getAllReviews']);
        Route::apiResource('taxi-requests', RequestTaxiController::class)->except(['store']);
        Route::apiResource('tour-plans', PlanTourController::class)->except(['store']);

    });

    // Staff and admin routes
    Route::middleware('role:admin,staff')->group(function () {});
});
