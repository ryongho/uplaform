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

Route::post('/user/regist/', [UserController::class, 'regist_user']); // 유저 등록
Route::post('/partner/regist', [UserController::class, 'regist_partner']); // 파트너 등록 
Route::post('/user/login', [UserController::class, 'login']);// 로그인
Route::post('/user/sns_login', [UserController::class, 'sns_login']);// sns 로그인
Route::get('login', [UserController::class, 'not_login'])->name('login');// 비로그인 시 
Route::get('/su', [UserController::class, 'su']);// 슈펴로그인
Route::middleware('auth:sanctum')->post('/user/logout', [UserController::class, 'logout']); // 로그아웃
Route::middleware('auth:sanctum')->get('/user/login_check', [UserController::class, 'login_check']); // 로그인 상태 체크
Route::put('/user/find_id', [UserController::class, 'find_id']); // 아이디 찾기
Route::put('/user/find_password', [UserController::class, 'find_password']);//비밀번호 찾기

Route::middleware('auth:sanctum')->get('/user/info', [UserController::class, 'info']); //유저 정보 가져오기
Route::middleware('auth:sanctum')->get('/user/partner_info', [UserController::class, 'partner_info']); //파트너 정보 가져오기
Route::middleware('auth:sanctum')->get('/user/area_info', [UserController::class, 'area_info']); //회원 추가 정보 가져오기

Route::middleware('auth:sanctum')->put('/user/update', [UserController::class, 'update']);// 유저정보 업데이트
Route::middleware('auth:sanctum')->put('/user/leave', [UserController::class, 'leave']); // 회원 탈퇴

Route::middleware('auth:sanctum')->get('/partner/info', [UserController::class, 'partner_info']); //파트너 정보
Route::middleware('auth:sanctum')->put('/partner/update', [UserController::class, 'partner_update']); // 파트너 정보 업데이트

Route::middleware('auth:sanctum')->post('/image/upload', [ImageController::class, 'upload']); // 이미지 업로드

Route::middleware('auth:sanctum')->post('/device/regist', [DeviceController::class, 'regist']);//디바이스 정보 등록

Route::middleware('auth:sanctum')->post('/reservation/regist/cs', [ReservationController::class, 'regist_cs']); //음식점 위생관리 예약
Route::middleware('auth:sanctum')->post('/reservation/regist/cr', [ReservationController::class, 'regist_cr']); // 공간 정리 예약 
Route::middleware('auth:sanctum')->post('/reservation/regist/lc', [ReservationController::class, 'regist_lc']); // 정리 교육 예약

Route::middleware('auth:sanctum')->put('/reservation/payment', [ReservationController::class, 'payment']); // 결제하기

Route::middleware('auth:sanctum')->get('/reservation/list/ing', [ReservationController::class, 'list_ing']);// 진행중 예약 리스트
Route::middleware('auth:sanctum')->get('/reservation/list/end', [ReservationController::class, 'list_end']);// 지난 예약 리스트
Route::middleware('auth:sanctum')->get('/reservation/detail', [ReservationController::class, 'detail']);// 예약 상세 내용
Route::middleware('auth:sanctum')->put('/reservation/cancel', [ReservationController::class, 'cancel']);// 예약 취소
Route::middleware('auth:sanctum')->get('/reservation/payment/list', [ReservationController::class, 'payment_list']);// 결제 내역
/*
Route::middleware('auth:sanctum')->post('/service/regist', [ServiceController::class, 'regist']); //서비스 지원
Route::middleware('auth:sanctum')->get('/service/list/ing', [ServiceController::class, 'list_ing']);// 진행중 서비스 리스트
Route::middleware('auth:sanctum')->get('/service/list/end', [ServiceController::class, 'list_end']);// 지난 서비스 리스트
Route::middleware('auth:sanctum')->get('/service/detail', [ServiceController::class, 'detail']);// 서비스 상세 내용
Route::middleware('auth:sanctum')->put('/service/cancel', [ServiceController::class, 'cancel']);// 서비스 취소
Route::middleware('auth:sanctum')->get('/service/sale/list', [ReservationController::class, 'sale_list']);// 정산 내역
Route::middleware('auth:sanctum')->get('/service/sale/detail', [ReservationController::class, 'sale_detail']);// 정산 내역 상세
*/

Route::middleware('auth:sanctum')->post('/card/regist', [CardController::class, 'regist']); //카드 등록
Route::middleware('auth:sanctum')->get('/card/list', [CardController::class, 'list']); //카드 리스트
Route::middleware('auth:sanctum')->put('/card/regist/default_card', [CardController::class, 'regist_default_card']); //기본카드 등록

Route::middleware('auth:sanctum')->put('/push/regist', [PushController::class, 'regist']); // 푸시 등록
Route::middleware('auth:sanctum')->get('/push/list', [PushController::class, 'list']); // 푸시 리스트

Route::middleware('auth:sanctum')->put('/notice/regist', [NoticeController::class, 'regist']); // 공지 등록
Route::get('/notice/list', [NoticeController::class, 'list']); // 공지 리스트
Route::get('/notice/detail', [NoticeController::class, 'detail']); // 공지 내용 
Route::middleware('auth:sanctum')->put('/notice/update', [NoticeController::class, 'update']);//공지 수정

Route::middleware('auth:sanctum')->put('/faq/regist', [FaqController::class, 'regist']); //faq 등록
Route::get('/faq/list', [FaqController::class, 'list']);//faq 리스트
Route::get('/faq/detail', [FaqController::class, 'detail']); //faq 내용
Route::middleware('auth:sanctum')->put('/faq/update', [FaqController::class, 'update']);//faq 수정
/*
Route::middleware('auth:sanctum')->put('/qna/regist', [QnaController::class, 'regist']);
Route::get('/qna/list', [QnaController::class, 'list']);
Route::get('/qna/detail', [QnaController::class, 'detail']);
Route::middleware('auth:sanctum')->put('/qna/update', [QnaController::class, 'update']);
*/

/*
Route::middleware('auth:sanctum')->get('/partner/list', [PartnerController::class, 'list']);
Route::middleware('auth:sanctum')->get('/user/list', [UserController::class, 'list']);
Route::put('/user/update_password', [UserController::class, 'update_password']);
Route::middleware('auth:sanctum')->put('/user/update_info', [UserController::class, 'update_info']);

Route::middleware('auth:sanctum')->put('/policy/regist', [PolicyController::class, 'regist']);
Route::get('/policy/detail', [PolicyController::class, 'detail']);
Route::get('/policy/list', [PolicyController::class, 'list']);
Route::middleware('auth:sanctum')->put('/policy/update', [PolicyController::class, 'update']);

Route::get('/login_check_partner', [PartnerController::class, 'login_check']);
*/





