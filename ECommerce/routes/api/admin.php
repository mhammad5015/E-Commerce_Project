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
//------------------------------------------------------------------------------------------
// PRODUCT
Route::post('product/add_product/{category_id}', [ProductController::class, 'add_product']);
Route::post('product/add_variants/{product_id}', [ProductController::class, 'add_variants']);
Route::delete('product/delete_product/{id}', [ProductController::class, 'delete_product']);
Route::get('product/product_profile/{id}', [ProductController::class, 'product_profile']);
Route::get('product/get_all_products', [ProductController::class, 'get_all_products']);
Route::get('product/get_admin_products/{admin_id}', [ProductController::class, 'get_admin_products']);
Route::get('product/get_all_tags', [ProductController::class, 'get_all_tags']);
Route::get('product/get_tag_products/{tag_id}', [ProductController::class, 'get_tag_products']);

Route::post('product/aprove_product', [ProductController::class, 'aprove_product']);
Route::get('product/get_pending_products', [ProductController::class, 'get_pending_products']);

Route::post('product/add_tag/{product_id}', [ProductController::class, 'add_tag']);
Route::post('product/add_discount/{product_id}', [ProductController::class, 'add_discount']);

Route::post('product/add_color', [ProductController::class, 'add_color']);
Route::post('product/add_size/{type_id}', [ProductController::class, 'add_size']);
Route::get('product/get_colors', [ProductController::class, 'get_colors']);

Route::get('admin/get', [HomeController::class, 'getAllAdmins']);
Route::get('admin/adminProfile/{id}', [HomeController::class, 'adminProfile']);
Route::get('admin/adminsCount', [HomeController::class, 'adminsCount']);
Route::get('admin/getAdminWallet', [HomeController::class, 'getAdminWallet']);
//------------------------------------------------------------------------------------------

Route::get('admin/get_category_productForAdmin/{admin_id}', [CategoryController::class, 'get_Categories_WithProductsForAdmin']);
Route::get('admin/get_all_categories_with_produts', [CategoryController::class, 'get_all_categories_with_produts']);
