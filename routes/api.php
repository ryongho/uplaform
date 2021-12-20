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
use App\Http\Controllers\NoticeController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\PolicyController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\PushController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\SMSController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\QuantityController;
use App\Http\Controllers\MailController;

use App\Models\User;


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
Route::get('login', [UserController::class, 'not_login'])->name('login');
Route::get('su', [UserController::class, 'su']);
Route::middleware('auth:sanctum')->post('/logout', [UserController::class, 'logout']);
Route::middleware('auth:sanctum')->get('/login_check', [UserController::class, 'login_check']);
Route::post('/check_email', [UserController::class, 'check_email']);
Route::post('/check_user', [UserController::class, 'check_user']);
Route::post('/check_nickname', [UserController::class, 'check_nickname']);
Route::get('/user/certifications', [UserController::class, 'certifications']);

Route::put('/partner/regist', [PartnerController::class, 'regist']);
Route::middleware('auth:sanctum')->get('/partner/list', [PartnerController::class, 'list']);
Route::middleware('auth:sanctum')->get('/user/list', [UserController::class, 'list']);
Route::middleware('auth:sanctum')->get('/user/info', [UserController::class, 'info']);
Route::middleware('auth:sanctum')->put('/user/update', [UserController::class, 'update']);
Route::put('/user/update_password', [UserController::class, 'update_password']);
Route::middleware('auth:sanctum')->put('/user/update_info', [UserController::class, 'update_info']);
Route::middleware('auth:sanctum')->put('/user/leave', [UserController::class, 'leave']);
Route::post('/partner/login', [PartnerController::class, 'login']);

Route::middleware('auth:sanctum')->post('/hotel/regist', [HotelController::class, 'regist']);
Route::get('/hotel/list', [HotelController::class, 'list']);
Route::middleware('auth:sanctum')->get('/hotel/list_by_partner', [HotelController::class, 'list_by_partner']);
Route::get('/hotel/detail', [HotelController::class, 'detail']);
Route::middleware('auth:sanctum')->put('/hotel/update', [HotelController::class, 'update']);
Route::middleware('auth:sanctum')->put('/hotel/image_update', [HotelController::class, 'image_update']);
Route::middleware('auth:sanctum')->delete('/hotel/image_delete', [HotelController::class, 'image_delete']);

Route::middleware('auth:sanctum')->post('/room/regist', [RoomController::class, 'regist']);
Route::get('/room/list', [RoomController::class, 'list']);
Route::middleware('auth:sanctum')->get('/room/list_for_select', [RoomController::class, 'list_for_select']);
Route::get('/room/list_by_hotel', [RoomController::class, 'list_by_hotel']);
Route::middleware('auth:sanctum')->get('/room/list_by_partner', [RoomController::class, 'list_by_partner']);
Route::get('/room/detail', [RoomController::class, 'detail']);
Route::middleware('auth:sanctum')->put('/room/update', [RoomController::class, 'update']);
Route::middleware('auth:sanctum')->put('/room/image_update', [RoomController::class, 'image_update']);
Route::middleware('auth:sanctum')->delete('/room/image_delete', [RoomController::class, 'image_delete']);
Route::middleware('auth:sanctum')->delete('/room/delete', [RoomController::class, 'delete']);


Route::middleware('auth:sanctum')->post('/goods/regist', [GoodsController::class, 'regist']);
Route::get('/goods/list', [GoodsController::class, 'list']);
Route::get('/goods/get_qty', [GoodsController::class, 'get_qty']);
Route::get('/goods/get_qty_list', [GoodsController::class, 'get_qty_list']);
Route::get('/goods/list_by_hotel', [GoodsController::class, 'list_by_hotel']);
Route::middleware('auth:sanctum')->get('/goods/list_by_partner', [GoodsController::class, 'list_by_partner']);
Route::get('/goods/detail', [GoodsController::class, 'detail']);
Route::middleware('auth:sanctum')->put('/goods/update', [GoodsController::class, 'update']);
Route::middleware('auth:sanctum')->put('/goods/update_by_key', [GoodsController::class, 'update_by_key']);
Route::middleware('auth:sanctum')->put('/goods/image_update', [GoodsController::class, 'image_update']);
Route::middleware('auth:sanctum')->delete('/goods/image_delete', [GoodsController::class, 'image_delete']);
Route::middleware('auth:sanctum')->delete('/goods/delete', [GoodsController::class, 'delete']);

Route::middleware('auth:sanctum')->put('/review/regist', [ReviewController::class, 'regist']);
Route::get('/review/detail', [ReviewController::class, 'detail']);
Route::get('/review/list', [ReviewController::class, 'list']);

Route::middleware('auth:sanctum')->put('/wish/toggle', [WishController::class, 'toggle']);
Route::middleware('auth:sanctum')->get('/wish/list', [WishController::class, 'list']);
Route::middleware('auth:sanctum')->put('/wish/compare', [WishController::class, 'compare']);
Route::middleware('auth:sanctum')->put('/wish/regist', [WishController::class, 'regist']);


Route::middleware('auth:sanctum')->put('/push/regist', [PushController::class, 'regist']);
Route::middleware('auth:sanctum')->get('/push/list', [PushController::class, 'list']);

Route::middleware('auth:sanctum')->put('/viewlog/regist', [ViewlogController::class, 'regist']);

Route::middleware('auth:sanctum')->put('/notice/regist', [NoticeController::class, 'regist']);
Route::get('/notice/list', [NoticeController::class, 'list']);
Route::get('/notice/detail', [NoticeController::class, 'detail']);
Route::middleware('auth:sanctum')->put('/notice/update', [NoticeController::class, 'update']);

Route::middleware('auth:sanctum')->put('/event/regist', [EventController::class, 'regist']);
Route::get('/event/list', [EventController::class, 'list']);
Route::get('/event/detail', [EventController::class, 'detail']);

Route::middleware('auth:sanctum')->put('/faq/regist', [FaqController::class, 'regist']);
Route::get('/faq/list', [FaqController::class, 'list']);
Route::get('/faq/detail', [FaqController::class, 'detail']);
Route::middleware('auth:sanctum')->put('/faq/update', [FaqController::class, 'update']);

Route::middleware('auth:sanctum')->put('/policy/regist', [PolicyController::class, 'regist']);
Route::get('/policy/detail', [PolicyController::class, 'detail']);
Route::get('/policy/list', [PolicyController::class, 'list']);
Route::middleware('auth:sanctum')->put('/policy/update', [PolicyController::class, 'update']);

Route::middleware('auth:sanctum')->put('/reservation/regist', [ReservationController::class, 'regist']);
Route::middleware('auth:sanctum')->get('/reservation/detail', [ReservationController::class, 'detail']);
Route::middleware('auth:sanctum')->get('/reservation/list', [ReservationController::class, 'list']);
Route::middleware('auth:sanctum')->get('/reservation/list_by_user', [ReservationController::class, 'list_by_user']);
Route::middleware('auth:sanctum')->get('/reservation/list_by_hotel', [ReservationController::class, 'list_by_hotel']);
Route::middleware('auth:sanctum')->get('/reservation/list_by_goods', [ReservationController::class, 'list_by_goods']);
Route::middleware('auth:sanctum')->put('/reservation/cancel', [ReservationController::class, 'cancel']);
Route::middleware('auth:sanctum')->put('/reservation/cancel_by_partner', [ReservationController::class, 'cancel_by_partner']);
Route::middleware('auth:sanctum')->get('/reservation/list_cancel', [ReservationController::class, 'list_cancel']);
Route::middleware('auth:sanctum')->put('/reservation/update', [ReservationController::class, 'update']);
Route::middleware('auth:sanctum')->put('/reservation/request_confirm', [ReservationController::class, 'request_confirm']);
Route::middleware('auth:sanctum')->put('/reservation/confirm', [ReservationController::class, 'confirm']);


Route::middleware('auth:sanctum')->post('/image/upload', [ImageController::class, 'upload']);
Route::middleware('auth:sanctum')->post('/mail/send', [MailController::class, 'send']);

Route::middleware('auth:sanctum')->put('/recommend/regist', [RecommendController::class, 'regist']);
Route::get('/recommend/list', [RecommendController::class, 'list']);

Route::middleware('auth:sanctum')->put('/local/regist', [LocalController::class, 'regist']);
Route::get('/local/list', [LocalController::class, 'list']);

Route::middleware('auth:sanctum')->put('/device/regist', [DeviceController::class, 'regist']);
Route::middleware('auth:sanctum')->put('/quantity/update', [QuantityController::class, 'update']);

Route::get('/login_check_partner', [PartnerController::class, 'login_check']);

Route::middleware('auth:sanctum')->get('/login_check_user', function (Request $request) {

    //$result = auth('api')->check();
    //dd($result);
    //return $request->user();
    
});





