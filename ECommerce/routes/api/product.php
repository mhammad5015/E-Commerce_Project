<?php

use App\Http\Controllers\api\HomeController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\api\ProductController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['prefix' => 'admin', 'middleware' => ['auth:admin_api', 'scopes:admin']], function () {
});
// Route::post('product/add_product/{category_id}', [ProductController::class, 'add_product']);
Route::post('product/search_all_products', [ProductController::class, 'search_all_products']);
Route::post('product/search_admin_products/{admin_id}', [ProductController::class, 'search_admin_products']);
Route::post('product/add_type', [ProductController::class, 'add_type']);
Route::get('product/get_types', [ProductController::class, 'get_types']);
Route::get('product/get_type_sizes/{type_id}', [ProductController::class, 'get_type_sizes']);
Route::get('product/get_highest_sellcount', [HomeController::class, 'get_highest_sellcount']);

