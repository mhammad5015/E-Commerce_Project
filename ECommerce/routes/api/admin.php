<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\AuthController;
use App\Http\Controllers\api\HomeController;
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




Route::post('admin/register',[AuthController::class, 'adminRegister']);
Route::post('admin/login',[AuthController::class, 'adminLogin']);

Route::group( ['prefix' => 'admin','middleware' => ['auth:admin_api','scopes:admin'] ],function(){
    Route::post('logout',[AuthController::class, 'adminLogout']);
 });

 Route::post('admin/password/email',[AuthController::class, 'admin_forgetPassword']);
 Route::post('admin/code/check',[AuthController::class, 'checkCode']);
 Route::post('admin/password/reset',[AuthController::class, 'adminResetPassword']);

Route::get('admin/get',[HomeController::class, 'getAdmins']);
Route::post('admin/{id}', [HomeController::class, 'getAdmin_details']);

Route::post('add_product', [ProductController::class, 'add_product']);