<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\Reservation;
use App\Models\Apply;
use App\Models\User;
use App\Models\PartnerInfo;
use App\Models\Pay;
use App\Models\Fee;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class ApplyController extends Controller
{
    public function regist(Request $request)
    {
    
        $return = new \stdClass;

        $return->status = "500";
        $return->msg = "관리자에게 문의";
        

        $login_user = Auth::user();
        $user_id = $login_user->getId();

        $result = Apply::insertGetId([
            'user_id' => $user_id,
            'reservation_id'=> $request->reservation_id ,
            'status'=> "A" ,
            'created_at'=> Carbon::now(),
        ]);


        if($result){ //DB 입력 성공

            $return->status = "200";
            $return->msg = "success";
            $return->insert_id = $result ;
            
        }else{
            $return->status = "500";
            $return->msg = "fail";
        }

        return response()->json($return, 200)->withHeaders([
            'Content-Type' => 'application/json'
        ]);;
    }

    public function list(Request $request){

        
        $rows = apply::join('reservations', 'reservations.id', '=', 'applies.reservation_id')
                        ->join('partner_infos', 'partner_infos.user_id', '=', 'applies.user_id')        
                        ->select(
                                'applies.id as reservation_id',   
                                'applies.id as apply_id',
                                'partner_infos.ceo_name',
                                'partner_infos.partner_type',
                                'partner_infos.address',
                                'partner_infos.activity_distance',
                                'partner_infos.license_img',
                                'partner_infos.reg_no',
                                'partner_infos.biz_type',
                                'partner_infos.position',
                                'partner_infos.biz_name',
                                'partner_infos.address2',
                                'partner_infos.tel',                                
                        )         
                        ->where('reservations.id', $request->reservation_id)
                        ->get();
        
        $return = new \stdClass;

        $return->status = "200";
        $return->data = $rows ;

        return response()->json($return, 200)->withHeaders([
            'Content-Type' => 'application/json'
        ]);

    }

    public function list_by_user(Request $request){

        $login_user = Auth::user();
        $user_id = $login_user->id;

        $s_no = $request->start_no;
        $row = $request->row;
        $type = $request->type;

        $rows = apply::join('reservations', 'reservations.id', '=', 'applies.reservation_id')
                        ->select(   
                                'applies.id as apply_id',
                                'reservations.reservation_type',
                                'reservations.service_date',
                                'reservations.service_time',
                                'reservations.learn_day',
                                'applies.status',    
                        )         
                        ->where('applies.id' ,">", $s_no)
                        ->where('applies.user_id', $user_id)
                        ->when($type, function ($query, $type) {
                            if($type == "ing"){
                                return $query->whereIn('applies.status', ['A']);
                            }else if($type == "end"){
                                return $query->whereIn('applies.status', ['S','C','E']);
                            }
                            
                        })
                        ->limit($row)->get();
        
        $cnt = apply::join('reservations', 'reservations.id', '=', 'applies.reservation_id')
        ->where('applies.user_id', $user_id)
        ->when($type, function ($query, $type) {
            if($type == "ing"){
                return $query->whereIn('applies.status', ['A']);
            }else if($type == "end"){
                return $query->whereIn('applies.status', ['S','C']);
            }
            
        })->count();
        $return = new \stdClass;

        $return->status = "200";
        $return->cnt = $cnt;
        $return->data = $rows ;

        return response()->json($return, 200)->withHeaders([
            'Content-Type' => 'application/json'
        ]);

    }

    
    public function detail(Request $request){
    
        $id = $request->apply_id;

        $rows = apply::join('reservations', 'reservations.id', '=', 'applies.reservation_id')
                        ->select(      
                                'applies.id as apply_id',
                                'reservations.reservation_type',
                                'reservations.service_date',
                                'reservations.service_time',
                                'reservations.learn_day',
                                'applies.status',
                                'reservations.reservation_no',    
                                'reservations.service_addr',
                                'reservations.memo',
                                'reservations.phone',
                                'reservations.service_detail',
                                'reservations.learn_day',
                                'reservations.price',
                                'reservations.created_at',
                                'reservations.finished_at',
                                'reservations.services',
                                'applies.canceled_at',
                                'matched_at',
                                'applies.cancel_comment',
                                'service_comment',

                        )         
                        ->where('applies.id' , $id)
                        ->first();

        $return = new \stdClass;

        $return->status = "200";
        $return->data = $rows ;

        return response()->json($return, 200)->withHeaders([
            'Content-Type' => 'application/json'
        ]);


    }


    public function cancel(Request $request){
        //dd($request);
        $return = new \stdClass;
        $login_user = Auth::user();

        $user_id = $login_user->id;

        $apply_info = Apply::where('id', $request->apply_id)->where('user_id', $user_id)->first();
        
        if(!$apply_info){
            $return->status = "601";
            $return->msg = "유효한 지원 정보가 아닙니다.";
            $return->apply_id = $request->apply_id;
        }else if($apply_info->status == "C"){
            $return->status = "602";
            $return->msg = "이미 취소 처리된 지원입니다.";
            $return->apply_id = $request->apply_id;
        }else{
            
            $result = Apply::where('id', $request->apply_id)->where('user_id', $user_id)
                    ->update([
                        'status' => 'C',
                        'canceled_at' => Carbon::now(),
                        'cancel_comment' => $request->comment,
                    ]); // 취소 
            if(!$result){
                $return->status = "500";
                $return->msg = "취소처리 실패 실패";
            }else{
                $return->status = "200";
                $return->msg = "취소 완료";
            }
        }

    
        return response()->json($return, 200)->withHeaders([
            'Content-Type' => 'application/json'
        ]);;

    }

    public function complete(Request $request){
        //dd($request);
        $return = new \stdClass;
        $login_user = Auth::user();

        $user_id = $login_user->id;

        $apply_info = Apply::where('id', $request->apply_id)->where('user_id', $user_id)->first();
        $reservation_info = Reservation::where('id', $apply_info->reservation_id)->first();

        $fee_info = Fee::where('type', $reservation_info->reservation_type)->first();
        
        if(!$apply_info){
            $return->status = "601";
            $return->msg = "유효한 지원 정보가 아닙니다.";
            $return->apply_id = $request->apply_id;
        }else if($apply_info->status == "C"){
            $return->status = "602";
            $return->msg = "취소 처리된 지원입니다.";
            $return->apply_id = $request->apply_id;
        }else{
            
            $result = Apply::where('id', $request->apply_id)->where('user_id', $user_id)
                    ->update([
                        'status' => 'E',
                        'service_comment' => $request->comment,
                        'serviced_at' => Carbon::now(),
                    ]); // 완료
                   

            
            $result2 = Pay::insert([
                'user_id' => $user_id,
                'reservation_id'=> $apply_info->reservation_id ,
                'state'=> "W" ,
                'price'=> $reservation_info->price ,
                'amount'=> ($reservation_info->price * ((100-$fee_info->fee) / 100)) ,
                'created_at'=> Carbon::now(),
            ]);

            if(!$result || !$result2){
                $return->status = "500";
                $return->msg = "완료 처리 실패";
            }else{
                $return->status = "200";
                $return->msg = "처리 완료";
            }
        }

    
        return response()->json($return, 200)->withHeaders([
            'Content-Type' => 'application/json'
        ]);;

    }

    

    public function update(Request $request){
        //dd($request);
        $return = new \stdClass;

        $return->status = "200";
        $return->msg = "변경 완료";
        $return->key = $request->key;
        $return->value = $request->value;
        $return->updated_id = $request->id;

        $login_user = Auth::user();
        $user_id = $login_user->id;

        $key = $request->key;
        $value = $request->value;
        $id = $request->id;

        $result = Reservation::where('id', $id)->update([$key => $value, 'update_user' => $user_id]);

        if(!$result){
            $return->status = "500";
            $return->msg = "변경 실패";
        }

        return response()->json($return, 200)->withHeaders([
            'Content-Type' => 'application/json'
        ]);;

    }

    public function match(Request $request){
        //dd($request);
        $return = new \stdClass;

        $return->status = "200";
        $return->msg = "매칭 완료";
    
        $result = Reservation::where('id', $request->reservation_id)->update(['status' => 'R']);
        $result2 = apply::where('id', $request->apply_id)->update(['status' => 'S','matched_at' => Carbon::now()]);


        if(!$result){
            $return->status = "500";
            $return->msg = "변경 실패";
        }

        return response()->json($return, 200)->withHeaders([
            'Content-Type' => 'application/json'
        ]);;

    }

    public function rematch(Request $request){
        //dd($request);
        $return = new \stdClass;

        $return->status = "200";
        $return->msg = "재매칭 완료";

        $result = apply::where('id', $request->old_apply_id)->update(['status' => 'W','matched_at' => '2022-01-01 00:00:00']);
        $result2 = apply::where('id', $request->new_apply_id)->update(['status' => 'S','matched_at' => Carbon::now()]);


        if(!$result2){
            $return->status = "500";
            $return->msg = "변경 실패";
        }

        return response()->json($return, 200)->withHeaders([
            'Content-Type' => 'application/json'
        ]);;

    }


    

    



}
