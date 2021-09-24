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
use App\Http\Controllers\ImageController;
use App\Http\Controllers\RecommendController;
use App\Http\Controllers\LocalController;

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

/*Route::middleware('auth:api')->put('/partner/hotel/regist', function (Request $request) {
    //return $request->partner();
});*/

Route::put('/regist', [UserController::class, 'regist']);
Route::post('/login', [UserController::class, 'login']);
Route::post('/logout', [UserController::class, 'logout']);
//Route::post('/login_check', [UserController::class, 'login_check']);
Route::post('/check_email', [UserController::class, 'check_email']);
Route::post('/check_nickname', [UserController::class, 'check_nickname']);

Route::put('/partner/regist', [PartnerController::class, 'regist']);
Route::middleware('auth:sanctum')->get('/partner/list', [PartnerController::class, 'list']);
Route::middleware('auth:sanctum')->get('/user/list', [UserController::class, 'list']);
Route::middleware('auth:sanctum')->get('/user/info', [UserController::class, 'info']);
Route::post('/partner/login', [PartnerController::class, 'login']);

Route::middleware('auth:sanctum')->post('/hotel/regist', [HotelController::class, 'regist']);
Route::middleware('auth:sanctum')->get('/hotel/list', [HotelController::class, 'list']);
Route::middleware('auth:sanctum')->get('/hotel/list_by_partner', [HotelController::class, 'list_by_partner']);
Route::middleware('auth:sanctum')->get('/hotel/detail', [HotelController::class, 'detail']);

Route::middleware('auth:sanctum')->post('/room/regist', [RoomController::class, 'regist']);
Route::middleware('auth:sanctum')->get('/room/list', [RoomController::class, 'list']);
Route::middleware('auth:sanctum')->get('/room/list_for_select', [RoomController::class, 'list_for_select']);
Route::middleware('auth:sanctum')->get('/room/list_by_hotel', [RoomController::class, 'list_by_hotel']);
Route::middleware('auth:sanctum')->get('/room/list_by_partner', [RoomController::class, 'list_by_partner']);
Route::middleware('auth:sanctum')->get('/room/detail', [RoomController::class, 'detail']);

Route::middleware('auth:sanctum')->post('/goods/regist', [GoodsController::class, 'regist']);
Route::middleware('auth:sanctum')->get('/goods/list', [GoodsController::class, 'list']);
Route::middleware('auth:sanctum')->get('/goods/list_by_hotel', [GoodsController::class, 'list_by_hotel']);
Route::middleware('auth:sanctum')->get('/goods/list_by_partner', [GoodsController::class, 'list_by_partner']);
Route::middleware('auth:sanctum')->get('/goods/detail', [GoodsController::class, 'detail']);

Route::middleware('auth:sanctum')->put('/review/regist', [ReviewController::class, 'regist']);
Route::get('/review/list', [ReviewController::class, 'list']);

Route::middleware('auth:sanctum')->put('/wish/toggle', [WishController::class, 'toggle']);
Route::middleware('auth:sanctum')->get('/wish/list', [WishController::class, 'list']);


Route::middleware('auth:sanctum')->put('/wish/regist', [WishController::class, 'regist']);

Route::middleware('auth:sanctum')->put('/viewlog/regist', [ViewlogController::class, 'regist']);

Route::middleware('auth:sanctum')->post('/image/upload', [ImageController::class, 'upload']);

Route::middleware('auth:sanctum')->put('/recommend/regist', [RecommendController::class, 'regist']);
Route::middleware('auth:sanctum')->get('/recommend/list', [RecommendController::class, 'list']);

Route::middleware('auth:sanctum')->put('/local/regist', [LocalController::class, 'regist']);
Route::middleware('auth:sanctum')->get('/local/list', [LocalController::class, 'list']);




Route::get('/login_check_partner', [PartnerController::class, 'login_check']);

Route::middleware('auth:sanctum')->get('/login_check_user', function (Request $request) {

    //$result = auth('api')->check();
    //dd($result);
    //return $request->user();
    
});





