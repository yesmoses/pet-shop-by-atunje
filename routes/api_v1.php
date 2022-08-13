<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\V1\UsersController;
use App\Http\Controllers\V1\UserAuthController;
use App\Http\Controllers\V1\AdminAuthController;
use App\Http\Controllers\V1\UserProfileController;



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

Route::get('/', function () {
    return ['message' => 'Welcome', 'success' => 1];
});

Route::group(['prefix'=>'admin'], function() {
    Route::post('create', [AdminAuthController::class, 'register']);
    Route::post('login', [AdminAuthController::class, 'login']);

    Route::group(['middleware' => ['auth:api']], function() {
        Route::get('logout', [AdminAuthController::class, 'logout']);

        Route::post('user-listing', [UsersController::class, 'users']);
    });
});

Route::group(['prefix'=>'user'], function() {
    Route::post('create', [UserAuthController::class, 'create']);
    Route::post('login', [UserAuthController::class, 'login']);

    Route::group(['middleware' => ['auth:api']], function() {
        Route::get('logout', [UserAuthController::class, 'logout']);

        Route::get('/', [UserProfileController::class, 'show']);
        Route::delete('/', [UserProfileController::class, 'delete']);
        Route::put('/edit', [UserProfileController::class, 'update']);
    });
});
