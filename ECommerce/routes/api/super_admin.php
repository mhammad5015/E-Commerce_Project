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

Route::group(['prefix' => 'super_admin', 'middleware' => ['auth:admin_api', 'scopes:super_admin']], function () {
    Route::post('logout', [AuthController::class, 'super_adminLogout']);
});

Route::post('super_admin/add_admin',[AuthController::class, 'add_admin']);
