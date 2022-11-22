<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use \App\Http\Controllers\api\V1\general\AuthController;
use \App\Http\Controllers\api\V1\location\LocationController;
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


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::group(["prefix" => "v1"], function () {
    Route::post("registration", [AuthController::class, 'registration']);
    Route::get("login", [AuthController::class, 'login'])->name("login");
    Route::middleware('auth:sanctum')->group(function () {
        Route::put("/update-user", [AuthController::class, 'updateUserData']);
        Route::delete("/delete-user", [AuthController::class, 'deleteUser']);
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('get-users', [AuthController::class, 'getUsers']);
        Route::get('get-user/{id}', [AuthController::class, 'getUser']);
        Route::post('create-location', [LocationController::class, 'createLocation']);
        Route::put('update-location', [LocationController::class, 'updateLocation']);
        Route::delete('delete-location/{id}', [LocationController::class, 'deleteLocation']);
        Route::get('get-locations', [LocationController::class, 'getLocations']);
        Route::get('get-locations/{user_id}', [LocationController::class, 'getLocationsByUserId']);
        Route::get('get-locations-by-ip', [LocationController::class, 'getLocationsByIp']);

    });
});

