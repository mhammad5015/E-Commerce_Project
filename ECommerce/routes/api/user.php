<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\AuthController;
use App\Http\Controllers\api\HomeController;

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


Route::post('user/register', [AuthController::class, 'userRegister']);
Route::post('user/login', [AuthController::class, 'userLogin']);

Route::group(['prefix' => 'user', 'middleware' => ['auth:user_api', 'scopes:user']], function () {
    Route::post('logout', [AuthController::class, 'userLogout']);
});

Route::post('user/password/email', [AuthController::class, 'user_forgetPassword']);
Route::post('user/code/check', [AuthController::class, 'checkCode']);
Route::post('user/password/reset', [AuthController::class, 'userResetPassword']);

Route::get('user/get', [HomeController::class, 'getUsers']);
Route::post('user/{id}', [HomeController::class, 'getUser_details']);
