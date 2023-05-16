<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\AuthController;

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

Route::post('super_admin/login', [AuthController::class, 'super_adminLogin']);

Route::group(['prefix' => 'super_admin', 'middleware' => ['auth:admin_api', 'scopes:super_admin']], function () {
    Route::post('logout', [AuthController::class, 'super_adminLogout']);
});
