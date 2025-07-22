<?php

use App\Http\Controllers\Api\Vendor\Auth\RegisterController;
use App\Http\Controllers\Api\Vendor\Products\CategoryController;
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
        
        // Categories
            Route::get('/categories', [CategoryController::class, 'categories']);
            Route::get('categories/{id}', [CategoryController::class, 'specifications']);

        // Products
            Route::get('products', [ProductController::class, 'index']);
            Route::get('product/show/{id}', [ProductController::class, 'show']);
            Route::post('product/add', [ProductController::class, 'store']);
            Route::put('product/update/{id}', [ProductController::class, 'update']);
            Route::delete('product/delete/{id}', [ProductController::class, 'destroy']);
            Route::put('product/toggle/{id}', [ProductController::class, 'toggle']);

        // Orders
            Route::get('orders/list', [OrderController::class, 'index']);
            Route::get('orders/status/{status}', [OrderController::class, 'filterByStatus']);
            Route::get('orders/{order_id}', [OrderController::class, 'showOrder']);
            Route::put('orders/{order_id}/status', [OrderController::class, 'updateStatus']);
        
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
