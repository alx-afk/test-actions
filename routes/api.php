<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::post('/test1', [\App\Http\Controllers\Test::class, 'test1']);
Route::get('/test2', [\App\Http\Controllers\Test::class, 'test2']);
Route::get('/test3', [\App\Http\Controllers\Test::class, 'test3']);
