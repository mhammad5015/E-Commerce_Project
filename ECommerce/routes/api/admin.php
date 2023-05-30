<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\AuthController;
use App\Http\Controllers\api\CategoryController;
use App\Http\Controllers\api\HomeController;
use App\Http\Controllers\api\ProductController;





Route::group(['prefix' => 'admin', 'middleware' => ['auth:admin_api', 'scopes:admin']], function () {
    Route::post('logout', [AuthController::class, 'adminLogout']);
});

Route::get('admin/get', [HomeController::class, 'getAdmins']);
Route::post('admin/{id}', [HomeController::class, 'getAdmin_details']);
Route::get('admin/get_Total_Admin', [HomeController::class, 'get_Total_Admin']);
Route::get('admin/get_wallet_Admin', [HomeController::class, 'get_wallet_Admin']);


Route::post('admin/get_category_productForAdmin/{admin_id}', [CategoryController::class, 'get_Categories_WithProductsForAdmin']);

Route::post('admin/get_category_product/{admin_id}', [CategoryController::class, 'get_category_withProduct_Admin']);
Route::get('admin/get_category_parent/{category_id}', [CategoryController::class, 'get_Parent_Category']);
