<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\AuthController;
use App\Http\Controllers\api\CategoryController;
use App\Http\Controllers\api\HomeController;
use App\Http\Controllers\api\OrderController;

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
Route::group(['prefix' => 'super_admin', 'middleware' => ['auth:super_admin_api', 'scopes:super_admin']], function () {
    Route::post('logout', [AuthController::class, 'super_adminLogout']);
});
Route::post('super_admin/add_admin', [AuthController::class, 'add_admin']);

// Category
Route::post('super_admin/create_category', [CategoryController::class, 'create_category']);
Route::get('super_admin/get_category', [CategoryController::class, 'index']);
Route::delete('super_admin/delete_category/{id}', [CategoryController::class, 'delete']);
Route::post('super_admin/edit_category/{id}', [CategoryController::class, 'update']);
Route::get('super_admin/getAllChildren', [CategoryController::class, 'getAllChildren']);

// Ads
Route::post('super_admin/create_ads', [HomeController::class, 'store_ad']);
Route::delete('super_admin/delete_ads/{id}', [HomeController::class, 'delete_ads']);
Route::get('super_admin/get_ads', [HomeController::class, 'get_ads']);
///
Route::post('super_admin/updateState/{order_id}', [OrderController::class, 'updateState']);
