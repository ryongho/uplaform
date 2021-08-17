<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\UserController;
use App\Http\Controllers\PartnerController;
use App\Http\Controllers\HotelController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\GoodsController;
use App\Http\Controllers\WishController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ViewlogController;

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

Route::put('/regist', [UserController::class, 'regist']);
Route::post('/login', [UserController::class, 'login']);
Route::post('/logout', [UserController::class, 'logout']);
Route::post('/login_check', [UserController::class, 'login_check']);
Route::post('/find_user_id', [UserController::class, 'find_user_id']);

Route::put('/partner/regist', [PartnerController::class, 'regist']);
Route::get('/partner/list', [PartnerController::class, 'list']);
Route::post('/partner/login', [PartnerController::class, 'login']);

Route::put('/hotel/regist', [HotelController::class, 'regist']);
Route::put('/room/regist', [RoomController::class, 'regist']);
Route::put('/goods/regist', [GoodsController::class, 'regist']);
Route::put('/wish/regist', [WishController::class, 'regist']);
Route::put('/review/regist', [ReviewController::class, 'regist']);
Route::put('/viewlog/regist', [ViewlogController::class, 'regist']);


Route::middleware('auth:sanctum')->get('/token_check', function (Request $request) {
    return $request->user();
});




