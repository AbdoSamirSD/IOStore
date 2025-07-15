<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Auth\ForgotPasswordController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\Other\BannerController;
use App\Http\Controllers\Api\Other\CartController;
use App\Http\Controllers\Api\Other\CategoryController;
use App\Http\Controllers\Api\Other\FavoriteController;
use App\Http\Controllers\Api\Other\NotificationController;
use App\Http\Controllers\Api\Other\OrderController;
use App\Http\Controllers\Api\Other\ProductController;
use App\Http\Controllers\Api\Other\ProfileController;
use App\Http\Controllers\Api\Other\PromoCodeController;
use Illuminate\Support\Facades\Route;



Route::group([
    'middleware' => ['guest', 'throttle:6,1'],
    'prefix' => 'auth'
], function () {
    Route::post('/register', [RegisterController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [ForgotPasswordController::class, 'forgotPassword']);
    Route::post('/verify-reset-token', [ForgotPasswordController::class, 'verifyResetToken']);
    Route::post('/reset-password', [ForgotPasswordController::class, 'resetPassword']);
});

Route::group([
    'middleware' => ['auth:sanctum'],
    'prefix' => 'auth'
], function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
});
Route::group([
    'middleware' => ['guest'],
], function () {
    // categories
    Route::get('/categories', [CategoryController::class, 'categories']);
    Route::get('/main-categories', [CategoryController::class, 'mainCategories']);
    Route::get('/sub-categories', [CategoryController::class, 'subCategories']);
    // products
    Route::get('/products', [ProductController::class, 'products']);
    Route::get('/new-products', [ProductController::class, 'newProducts']);
    Route::get('/popular-products', [ProductController::class, 'popularProducts']);
    Route::get('/hot-offer-products', [ProductController::class, 'hotOfferProducts']);
    // banners
    Route::get('/banners/{type}', [BannerController::class, 'getBannersByType']);
});
Route::group([
    'middleware' => ['auth:sanctum'],
], function () {

    // favorites
    Route::get('/favorites', [FavoriteController::class, 'index']);
    Route::post('/toggle-favorite', [FavoriteController::class, 'toggleFavorite']);

    // cart
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart', [CartController::class, 'store']);
    Route::delete('/cart', [CartController::class, 'destroyAll']);
    Route::put('/cart/{id}', [CartController::class, 'update']);
    Route::delete('/cart/{id}', [CartController::class, 'destroy']);

    // orders
    Route::post('orders', [OrderController::class, 'store']);
    Route::get('orders', [OrderController::class, 'index']);
    Route::get('orders/{id}', [OrderController::class, 'show']);
    Route::post('orders/{id}/cancel', [OrderController::class, 'cancel']);

    // promo code
    Route::post('/promo-code/validate', [PromoCodeController::class, 'validatePromoCode']);


    // profile
    Route::post('/profile', [ProfileController::class, 'update']);
    Route::post('/profile/address', [ProfileController::class, 'updateAddress']);

    // notifications
    Route::group(['prefix' => 'notifications'], function () {
        Route::get('/', [NotificationController::class, 'index']);
    });

    // device token routes
    Route::group(['prefix' => 'device-tokens'], function () {
        Route::post('/', [NotificationController::class, 'storeDeviceToken']);
        Route::delete('/', [NotificationController::class, 'destroyDeviceToken']);
    });
});
