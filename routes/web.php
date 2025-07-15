<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::group([
    'prefix' => '{locale}',
    'where' => ['locale' => '[a-zA-Z]{2}'],
    'middleware' => 'setlocale',
], function () {
    Route::get('/', function () {
        return view('welcome');
    });
});
Route::get('/privacy-policy', function () {
    return view('privacy-policy');
})->name('privacy.policy');

//  Route::get('/migrate-refresh-seed', function() {
//     try {
//         // Running migrations with fresh option
//         Artisan::call('migrate');

//         // Running the database seeder
//         Artisan::call('db:seed');

//         return "Migration refreshed and seeding completed successfully.";
//     } catch (\Exception $e) {
//         return "Error: " . $e->getMessage();
//     }
// });



//create new order event
// Route::get('/create-order', function () {
//     try {
//         $order = new App\Models\Order();
//         event(new App\Events\NewOrderEvent($order));
//         return "Order created successfully.";
//     } catch (\Exception $e) {
//         return "Error: " . $e->getMessage();
//     }
// });
