<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Auth\ForgotPasswordController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\Other\BannerController;
use App\Http\Controllers\Api\Other\CartController;
use App\Http\Controllers\Api\Other\CategoryController;
use App\Http\Controllers\Api\Other\FavoriteController;
use App\Http\Controllers\Api\Other\OrderController;
use App\Http\Controllers\Api\Other\ProductController;
use App\Http\Controllers\Api\Other\ProfileController;
use App\Http\Controllers\Api\Other\PromoCodeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::options('{any}', function () {
    return response()->json([], 200);
})->where('any', '.*');

require __DIR__ . '/customerApi.php';

require __DIR__ . '/adminApi.php';