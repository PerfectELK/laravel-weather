<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CityController;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::apiResource('/cities', 'CityController')->only(['show']);
Route::get('/cities/geo/reverse/{lat}/{lon}', [CityController::class, 'reverseGeocode']);
Route::get('/cities/weather/{city}', [CityController::class, 'weather']);
