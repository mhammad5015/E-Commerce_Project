<?php

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


Route::post('product/add_product', [ProductController::class, 'add_product']);
Route::delete('product/delete_product/{id}', [ProductController::class, 'delete_product']);

Route::get('product/get_colors', [ProductController::class, 'get_colors']);
Route::get('product/get_sizes', [ProductController::class, 'get_sizes']);