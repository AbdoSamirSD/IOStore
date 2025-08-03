<?php

use App\Http\Controllers\Api\Vendor\Auth\RegisterController;
use App\Http\Controllers\Api\Vendor\Products\CategoryController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Vendor\Auth\AuthController;
use App\Http\Controllers\Api\Vendor\Dashboard\DashboardController;
use App\Http\Controllers\Api\Vendor\Profile\ProfileController;
use App\Http\Controllers\Api\Vendor\Products\ProductController;
use App\Http\Controllers\Api\Vendor\Products\VendorOrderController;
use App\Http\Controllers\Api\Vendor\Profile\WalletController;


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
            Route::post('product/update/{id}', [ProductController::class, 'update']);
            Route::delete('product/delete/{id}', [ProductController::class, 'destroy']);
            Route::put('product/toggle/{id}', [ProductController::class, 'toggle']);

        // Orders
            Route::get('orders/', [VendorOrderController::class, 'index']);
            Route::get('orders/getallstatuses', [VendorOrderController::class, 'getAllStatuses']);
            Route::get('orders/statistics', [VendorOrderController::class, 'statistics']);
            Route::get('order/status/{status}', [VendorOrderController::class, 'filterByStatus']);
            Route::get('order/{order_id}', [VendorOrderController::class, 'showOrder']);
            Route::put('order/{order_id}/status', [VendorOrderController::class, 'updateStatus']);
        
        // Vendo Wallet
            Route::get('wallet', [WalletController::class, 'wallet']);
            Route::get('wallet/summary', [WalletController::class, 'summary']);
            Route::get('wallet/transactions', [WalletController::class, 'transactions']);
            Route::get('wallet/transactiontypes', [WalletController::class, 'transactionTypes']);
            Route::get('wallet/transactions/{type}', [WalletController::class, 'filterByTransactionType']);
            Route::get('wallet/withdraw-requests', [WalletController::class, 'withdrawRequests']);
            Route::get('wallet/withdraw-requests/{id}', [WalletController::class, 'withdrawRequestDetails']);
            Route::post('wallet/request-withdraw', [WalletController::class, 'requestWithdraw']);
            
        // Notifications
            // Route::get('notifications', [NotificationController::class, 'index']);
            // Route::get('notifications/{id}', [NotificationController::class, 'show']);
            // Route::post('notifications/mark-as-read', [NotificationController::class, 'markAsRead']);
            // Route::post('notifications/mark-all-as-read', [NotificationController::class, 'markAllAsRead']);
    });
});
