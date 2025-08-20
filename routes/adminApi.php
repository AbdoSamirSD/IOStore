<?php

use App\Http\Controllers\Api\Admin\BannerController;
use App\Http\Controllers\Api\Admin\Categories\MainCategoriesController;
use App\Http\Controllers\Api\Admin\NotificationsController;
use App\Http\Controllers\Api\Admin\Orders\OrdersController;
use App\Http\Controllers\Api\Admin\Products\ProductsController;
use App\Http\Controllers\Api\Admin\ReportController;
use App\Http\Controllers\Api\Other\AppSettingsController;
use App\Http\Controllers\Api\Admin\Auth\AdminAuthController;
use App\Http\Controllers\Api\Admin\Vendor\VendorController;
use Illuminate\Support\Facades\Route;


Route::group(['prefix' => 'admin/auth'], function () {
    Route::post('/login', [AdminAuthController::class, 'login']);
});

Route::group([
    'middleware' => 'auth:admin',
    'prefix' => 'admin',
], function () {
    
    // orders routes
    Route::group(['prefix' => 'orders'], function () {
        Route::get('/', [OrdersController::class, 'index']);
        Route::get('/show/{order_number}', [OrdersController::class, 'show']);
        Route::post('/updatestatus/{order_number}', [OrdersController::class, 'updateStatus']);
        Route::get('/status/{status}', [OrdersController::class, 'filterByStatus']);
        Route::get('/search', [OrdersController::class, 'search']);
        Route::post('/daterange', [OrdersController::class, 'filterByDateRange']);
    });

    // Products routes
    Route::group(['prefix' => 'products'], function () {
        Route::get('/', [ProductsController::class, 'index']);
        Route::get('/search', [ProductsController::class, 'search']);
        Route::delete('/delete/{id}', [ProductsController::class, 'destroy']);
        Route::get('/pending', [ProductsController::class, 'pendingProducts']);
        Route::post('/updatestatus/{id}', [ProductsController::class, 'updateStatus']);
    });

    // Vendors routes
    Route::group(['prefix' => 'vendors'], function () {
        Route::get('/', [VendorController::class, 'index']);
        Route::get('show/{id}', [VendorController::class, 'show']);
        Route::delete('/delete/{id}', [VendorController::class, 'destroy']);
        Route::get('/pending', [VendorController::class, 'pendingVendors']);
        Route::post('/updatestatus/{id}', [VendorController::class, 'updateStatus']);
        Route::post('/commissionplans/{id}', [VendorController::class, 'setCommissionPlans']);
        Route::post('/commission/{id}', [VendorController::class, 'updateCommission']);
    });

    Route::group(['prefix' => 'report'], function () {
        Route::get('/', [ReportController::class, 'index']);
        Route::get('total-orders-revenue', [ReportController::class, 'totalOrdersAndRevenue']);
        Route::get('order-status-breakdown', [ReportController::class, 'orderStatusBreakdown']);
        Route::get('top-selling-products', [ReportController::class, 'topSellingProducts']);
        Route::get('discount-usage', [ReportController::class, 'discountUsage']);
    });

    // main categories
    Route::group(['prefix' => 'main-categories'], function () {

        Route::get('/', [MainCategoriesController::class, 'index']);
        Route::post('/', [MainCategoriesController::class, 'store']);
        Route::put('/{mainCategory}', [MainCategoriesController::class, 'update']);
        Route::delete('/{mainCategory}', [MainCategoriesController::class, 'destroy']);
    });

    // banners
    Route::group(['prefix' => 'banners'], function () {
        Route::get('/', [BannerController::class, 'index']);
        Route::post('/', [BannerController::class, 'store']);
        Route::put('/{banner}', [BannerController::class, 'update']);
        Route::delete('/{banner}', [BannerController::class, 'destroy']);
    });

    // notifications
    Route::group(['prefix' => 'notifications'], function () {
        Route::get('/', [NotificationsController::class, 'index']);
        Route::post('/save-device-token', [NotificationsController::class, 'saveDeviceToken']);
        Route::get('/get-device-token', [NotificationsController::class, 'getDeviceToken']);
    });

    Route::group(['prefix' => 'settings'], function () {
        Route::post('/set-app-version', [AppSettingsController::class, 'setAppVersion']);
        Route::get('/get-app-version', [AppSettingsController::class, 'getAppVersion']);
    });

    Route::post('/auth/logout', [AdminAuthController::class, 'logout']);
});
