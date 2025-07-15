<?php

use App\Http\Controllers\Api\Vendor\Auth\RegisterController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Vendor\Auth\AuthController;
use App\Http\Controllers\Api\Vendor\Dashboard\DashboardController;
use App\Http\Controllers\Api\Vendor\Profile\ProfileController;











Route::prefix('vendor')->group(function () {

    // Auth
    Route::post('register', [RegisterController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);

        // Dashboard
            Route::get('dashboard', [DashboardController::class, 'index']);

        // Profile
            Route::get('profile', [ProfileController::class, 'show']);
            Route::post('profile/update', [ProfileController::class, 'update']);
            Route::put('profile/changepassword', [ProfileController::class, 'changePassword']);
            Route::delete('profile/deleteaccount', [ProfileController::class, 'destroy']);

        // // Store Status
        //     Route::put('store-status', [StoreStatusController::class, 'update']);

        // // Products
        //     Route::get('products', [ProductController::class, 'index']);
        //     Route::post('products', [ProductController::class, 'store']);
        //     Route::put('products/{id}', [ProductController::class, 'update']);
        //     Route::delete('products/{id}', [ProductController::class, 'destroy']);
        //     Route::put('products/{id}/toggle', [ProductController::class, 'toggle']);

        // // Orders
        //     Route::get('orders', [OrderController::class, 'index']);
        //     Route::put('orders/{order_id}/status', [OrderController::class, 'updateStatus']);
        //     Route::put('orders/{order_id}/accept', [OrderController::class, 'acceptOrder']);
        //     Route::put('orders/{order_id}/reject', [OrderController::class, 'rejectOrder']);

        // // Notifications
        //     Route::get('notifications', [NotificationController::class, 'index']);

        // // Meta (Dropdown Lists)
        //     Route::get('meta', [MetaController::class, 'index']);
    });
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/vendor/test', function (Request $request) {
        return auth()->user(); // Will still return vendor
    });
});
