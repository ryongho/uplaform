<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\UserController;
use App\Http\Controllers\PartnerController;
use App\Http\Controllers\WishController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\NoticeController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\PolicyController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\PushController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\ApplyController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PayController;
use App\Http\Controllers\QnaController;
use App\Http\Controllers\AreaController;
use App\Http\Controllers\FcmController;
use App\Http\Controllers\AdminController;


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


Route::get('/admin/login', [AdminController::class, 'login']);
Route::get('/admin/logout', [AdminController::class, 'logout']);
Route::middleware('auth:sanctum')->get('/admin/reservation/list', [ReservationController::class, 'list']);
Route::middleware('auth:sanctum')->get('/admin/apply/list', [ApplyController::class, 'list']);
Route::middleware('auth:sanctum')->put('/admin/apply/match', [ApplyController::class, 'match']);

Route::post('/user/regist/', [UserController::class, 'regist_user']); // 유저 등록
Route::post('/partner/regist', [UserController::class, 'regist_partner']); // 파트너 등록 
Route::post('/partner/regist_info', [PartnerController::class, 'regist']); // 파트너 정보 추가
Route::post('/area/regist_info', [AreaController::class, 'regist']); // 유저 정보 추가
Route::post('/user/login', [UserController::class, 'login']);// 로그인
Route::post('/user/sns_login', [UserController::class, 'sns_login']);// sns 로그인
Route::get('login', [UserController::class, 'not_login'])->name('login');// 비로그인 시 
Route::get('/su', [UserController::class, 'su']);// 슈펴로그인
Route::middleware('auth:sanctum')->post('/user/logout', [UserController::class, 'logout']); // 로그아웃
Route::middleware('auth:sanctum')->get('/user/login_check', [UserController::class, 'login_check']); // 로그인 상태 체크
Route::put('/user/find_id', [UserController::class, 'find_id']); // 아이디 찾기


Route::middleware('auth:sanctum')->get('/user/info', [UserController::class, 'info']); //유저 정보 가져오기
Route::middleware('auth:sanctum')->get('/user/partner_info', [UserController::class, 'partner_info']); //파트너 정보 가져오기
Route::middleware('auth:sanctum')->get('/user/area_info', [UserController::class, 'area_info']); //회원 추가 정보 가져오기


Route::middleware('auth:sanctum')->put('/user/update', [UserController::class, 'update_user']);// 유저정보 업데이트
Route::middleware('auth:sanctum')->put('/partner/update', [UserController::class, 'update_partner']);// 파트너정보 업데이트
Route::middleware('auth:sanctum')->put('/user/leave', [UserController::class, 'leave']); // 회원 탈퇴
Route::middleware('auth:sanctum')->put('/user/change/type', [UserController::class, 'change_user_type']);// 유저타입 전환

Route::middleware('auth:sanctum')->put('/user/update/password', [UserController::class, 'update_password']);

Route::post('/image/upload', [ImageController::class, 'upload']); // 이미지 업로드

Route::get('/service/list', [ServiceController::class, 'list']);// 서비스 리스트

Route::middleware('auth:sanctum')->post('/device/regist', [DeviceController::class, 'regist']);//디바이스 정보 등록

Route::middleware('auth:sanctum')->post('/reservation/regist', [ReservationController::class, 'regist']); //예약하기

Route::middleware('auth:sanctum')->post('/payment/regist', [PaymentController::class, 'regist']); // 결제내역 등록
Route::middleware('auth:sanctum')->get('/payment/list/user', [PaymentController::class, 'list_by_user']);// 결제 리스트
Route::middleware('auth:sanctum')->get('/pay/list/user', [PayController::class, 'list_by_user']);// 정산 리스트
Route::middleware('auth:sanctum')->get('/pay/detail', [PayController::class, 'detail']);// 정산 상세

Route::middleware('auth:sanctum')->get('/reservation/list/user', [ReservationController::class, 'list_by_user']);// 예약 리스트
Route::middleware('auth:sanctum')->get('/reservation/detail', [ReservationController::class, 'detail']);// 예약 상세 내용
Route::middleware('auth:sanctum')->put('/reservation/cancel', [ReservationController::class, 'cancel']);// 예약 취소
Route::middleware('auth:sanctum')->get('/reservation/payment/list', [ReservationController::class, 'payment_list']);// 결제 내역
Route::middleware('auth:sanctum')->delete('/reservation/delete', [ReservationController::class, 'delete']);// 예약 삭제


Route::middleware('auth:sanctum')->get('/request/list/', [ReservationController::class, 'reqeust_list']);// 서비스 요청 리스트
Route::middleware('auth:sanctum')->get('/request/detail', [ReservationController::class, 'request_detail']);// 서비스 요청 상세 내용
Route::middleware('auth:sanctum')->post('/apply/regist', [ApplyController::class, 'regist']);// 지원하기
Route::middleware('auth:sanctum')->put('/apply/cancel', [ApplyController::class, 'cancel']);// 지원 취소
Route::middleware('auth:sanctum')->put('/apply/complete', [ApplyController::class, 'complete']);// 서비스완료 취소
Route::middleware('auth:sanctum')->get('/apply/list/user', [ApplyController::class, 'list_by_user']);// 예약 리스트
Route::middleware('auth:sanctum')->get('/apply/detail', [ApplyController::class, 'detail']);// 예약 상세 내용

Route::middleware('auth:sanctum')->post('/notice/regist', [NoticeController::class, 'regist']); // 공지 등록
Route::get('/notice/list', [NoticeController::class, 'list']); // 공지 리스트
Route::get('/notice/detail', [NoticeController::class, 'detail']); // 공지 내용 
Route::middleware('auth:sanctum')->put('/notice/update', [NoticeController::class, 'update']);//공지 수정

Route::middleware('auth:sanctum')->post('/push/regist', [PushController::class, 'regist']); // 푸시 등록
Route::middleware('auth:sanctum')->get('/push/list', [PushController::class, 'list']); // 푸시 리스트

Route::middleware('auth:sanctum')->put('/notice/regist', [NoticeController::class, 'regist']); // 공지 등록
Route::get('/notice/list', [NoticeController::class, 'list']); // 공지 리스트
Route::get('/notice/detail', [NoticeController::class, 'detail']); // 공지 내용 
Route::middleware('auth:sanctum')->put('/notice/update', [NoticeController::class, 'update']);//공지 수정

Route::middleware('auth:sanctum')->post('/faq/regist', [FaqController::class, 'regist']); //faq 등록
Route::get('/faq/list', [FaqController::class, 'list']);//faq 리스트
Route::get('/faq/detail', [FaqController::class, 'detail']); //faq 내용
Route::middleware('auth:sanctum')->put('/faq/update', [FaqController::class, 'update']);//faq 수정

Route::middleware('auth:sanctum')->post('/qna/regist', [QnaController::class, 'regist']);
Route::middleware('auth:sanctum')->get('/qna/list', [QnaController::class, 'list']);
Route::middleware('auth:sanctum')->get('/qna/detail', [QnaController::class, 'detail']);
Route::middleware('auth:sanctum')->put('/qna/update', [QnaController::class, 'update']);

Route::put('/fcm/update', [FcmController::class, 'update']);// fcm토큰 업데이트







