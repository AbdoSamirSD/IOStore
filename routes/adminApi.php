<?php

use App\Http\Controllers\Api\Admin\BannerController;
use App\Http\Controllers\Api\Admin\Categories\MainCategoriesController;
use App\Http\Controllers\Api\Admin\Categories\SubCategoriesController;
use App\Http\Controllers\Api\Admin\NotificationsController;
use App\Http\Controllers\Api\Admin\Orders\OrdersController;
use App\Http\Controllers\Api\Admin\Products\ProductsController;
use App\Http\Controllers\Api\Admin\ReportController;
use App\Http\Controllers\Api\Other\AppSettingsController;
use Illuminate\Support\Facades\Route;


Route::group([
    // 'middleware' => 'auth:api',
    'prefix' => 'admin',
], function () {

    Route::group(['prefix' => 'products'], function () {
        Route::get('/', [ProductsController::class, 'index']);
        Route::post('/', [ProductsController::class, 'store']);
        Route::put('/{product}', [ ProductsController::class, 'update']);
        Route::delete('/{product}', [ProductsController::class, 'destroy']);
        Route::post('/import', [ProductsController::class, 'import']);
    });
    Route::group(['prefix' => 'report'], function () {
        Route::get('/', [ReportController::class, 'index']);
        Route::get('total-orders-revenue', [ReportController::class, 'totalOrdersAndRevenue']);
        Route::get('order-status-breakdown', [ReportController::class, 'orderStatusBreakdown']);
        Route::get('top-selling-products', [ReportController::class, 'topSellingProducts']);
        Route::get('discount-usage', [ReportController::class, 'discountUsage']);
    });
    // orders routes
    Route::group(['prefix' => 'orders'], function () {
        Route::get('/', [OrdersController::class, 'index']);
        Route::get('/{order}', [OrdersController::class, 'show']);
        Route::put('/{order}/change-status', [OrdersController::class, 'changeStatus']);
    });


    // main categories
    Route::group(['prefix' => 'main-categories'], function () {

        Route::get('/', [MainCategoriesController::class, 'index']);
        Route::post('/', [MainCategoriesController::class, 'store']);
        Route::put('/{mainCategory}', [MainCategoriesController::class, 'update']);
        Route::delete('/{mainCategory}', [MainCategoriesController::class, 'destroy']);
    });

    // sub categories
    Route::group(['prefix' => 'sub-categories'], function () {
        Route::get('/', [SubCategoriesController::class, 'index']);
        Route::post('/', [SubCategoriesController::class, 'store']);
        Route::put('/{subCategory}', [SubCategoriesController::class, 'update']);
        Route::delete('/{subCategory}', [SubCategoriesController::class, 'destroy']);
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
});
