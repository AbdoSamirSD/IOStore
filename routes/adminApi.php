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
use App\Http\Controllers\Api\Admin\Specifications\SpecificationsController;
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
        Route::post('{id}/commissionplans', [VendorController::class, 'addCommissionPlans']);
        Route::delete('{id}/commissionplans/{planId}', [VendorController::class, 'deleteCommissionPlan']);
        Route::put('{id}/commissionplans/{planId}', [VendorController::class, 'updateCommissionPlans']);
    });
    
    // main categories
    Route::group(['prefix' => 'maincategories'], function () {

        Route::get('/', [MainCategoriesController::class, 'index']);
        Route::post('/', [MainCategoriesController::class, 'store']);
        Route::post('/update/{mainCategory}', [MainCategoriesController::class, 'update']);
        Route::delete('/{mainCategory}', [MainCategoriesController::class, 'destroy']);
    });

    // specifications routes
    Route::group(['prefix' => 'specifications'], function () {
        Route::get('/', [SpecificationsController::class, 'index']);
        Route::post('/', [SpecificationsController::class, 'store']);
        Route::post('/update/{id}', [SpecificationsController::class, 'update']);
        Route::delete('/{id}', [SpecificationsController::class, 'destroy']);

        Route::group(['prefix'=> 'values'], function () {
            Route::get('/{specificationId}', [SpecificationsController::class, 'indexValues']);
            Route::post('/{specificationId}', [SpecificationsController::class, 'storeValues']);
            Route::put('/{specificationId}', [SpecificationsController::class, 'updateValues']);
            Route::delete('/{valueId}', [SpecificationsController::class, 'destroyValues']);
            Route::delete('/delete-all/{specificationId}', [SpecificationsController::class, 'destroyAllValues']);
        });
        Route::group(['prefix'=> 'category'], function () {
            Route::get('/{categoryId}', [SpecificationsController::class, 'categorySpecifications']);
            Route::post('/{categoryId}', [SpecificationsController::class, 'addCategorySpecifications']);
            Route::put('/{categoryId}', [SpecificationsController::class, 'updateCategorySpecifications']);
            Route::delete('/{categoryId}', [SpecificationsController::class, 'destroyAllCategorySpecifications']);
            Route::delete('/{categoryId}/{specificationId}', [SpecificationsController::class, 'destroyCategorySpecifications']);
        });
    });

    // reports
    Route::group(['prefix' => 'statistics'], function () {
        Route::get('/', [ReportController::class, 'index']);
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
