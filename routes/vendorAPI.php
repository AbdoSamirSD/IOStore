<?php

use App\Http\Controllers\Api\Vendor\Auth\RegisterController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Vendor\Auth\AuthController;
use App\Http\Controllers\Api\Vendor\Dashboard\DashboardController;
use App\Http\Controllers\Api\Vendor\Profile\ProfileController;
use App\Http\Controllers\Api\Vendor\Products\ProductController;
use App\Http\Controllers\Api\Vendor\Products\OrderController;


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

        // Store Status
            Route::put('store-status', [ProfileController::class, 'updateStatus']);

        // Products
            Route::get('products', [ProductController::class, 'index']);
            Route::get('product/show/{id}', [ProductController::class, 'show']);
            Route::post('products/add', [ProductController::class, 'store']);
            Route::put('products/update/{id}', [ProductController::class, 'update']);
            Route::delete('products/delete/{id}', [ProductController::class, 'destroy']);
            Route::put('products/toggle/{id}', [ProductController::class, 'toggle']);

        // Orders
            Route::get('vendor/orders/list', [OrderController::class, 'index']);
            Route::get('vendor/orders/status/{status}', [OrderController::class, 'filterByStatus']);
            Route::get('vendor/orders/{order_id}', [OrderController::class, 'showOrder']);
            Route::put('vendor/orders/{order_id}/status', [OrderController::class, 'updateStatus']);
        
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
