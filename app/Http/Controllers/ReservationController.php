<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\Reservation;
use App\Models\Service;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class ReservationController extends Controller
{
    public function regist(Request $request)
    {
    
        $return = new \stdClass;

        $return->status = "500";
        $return->msg = "관리자에게 문의";
        

        $login_user = Auth::user();
        $user_id = $login_user->getId();

        $now = date('ymdHis');
        
        $reservation_no = "R_".$now."_".$user_id;

        $service_arr = explode(",",$request->services);
        $services = Service::whereIn('id',$service_arr )->get();
        $service_detail = "";

        foreach($services as $service){
            $service_detail .= $service->service_name." (".number_format($service->price)."원),";
        }

        if(isset($services )){
            $result = Reservation::insertGetId([
                'user_id' => $user_id,
                'reservation_type'=> $request->reservation_type ,
                'reservation_no'=> $reservation_no ,
                'services'=> $request->services ,
                'service_detail'=> $service_detail ,
                'service_date'=> $request->service_date ,
                'service_time'=> $request->service_time ,
                'service_addr'=> $request->service_addr ,
                'phone'=> $request->phone ,
                'memo'=> $request->memo ,
                'price'=> $request->price ,
                'learn_day'=> $request->learn_day ,
                'status'=> "W" ,
                'created_at'=> Carbon::now(),
            ]);

        }else{
            $result =  0;
        }
            
        

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

    public function list_by_user(Request $request){

        $login_user = Auth::user();
        $user_id = $login_user->id;

        $s_no = $request->start_no;
        $row = $request->row;
        $type = $request->type;

        $rows = Reservation::select(   
                                'id as reservation_id',
                                'reservation_type',
                                'service_date',
                                'service_time',
                                'status',    
                        )         
                        ->where('id' ,">", $s_no)
                        ->where('user_id', $user_id)
                        ->when($type, function ($query, $type) {
                            if($type == "ing"){
                                return $query->whereIn('status', ['W','R']);
                            }else if($type == "end"){
                                return $query->whereIn('status', ['S','C']);
                            }
                            
                        })
                        ->limit($row)->get();

        $return = new \stdClass;

        $return->status = "200";
        $return->cnt = count($rows);
        $return->data = $rows ;

        return response()->json($return, 200)->withHeaders([
            'Content-Type' => 'application/json'
        ]);

    }

    
    public function detail(Request $request){
    
        $id = $request->reservation_id;

        $rows = Reservation::select(   
                                'id as reservation_id',
                                'reservation_type',
                                'service_date',
                                'service_time',
                                'status',
                                'reservation_no',    
                                'service_addr',
                                'memo',
                                'phone',
                                'service_detail',
                                'learn_day',
                                'price',
                                'created_at',
                                'finished_at',
                        )         
                        ->where('id' , $id)
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

        $reservation_info = Reservation::where('id', $request->reservation_id)->first();

        if(!$reservation_info){
            $return->status = "601";
            $return->msg = "유효한 예약 정보가 아닙니다.";
            $return->reservation_id = $request->id;
        }else if($reservation_info->status == "C"){
            $return->status = "602";
            $return->msg = "이미 취소 처리된 예약입니다.";
            $return->reservation_id = $request->id;
        }else{
            
            $result = Reservation::where('id', $request->reservation_id)->update(['status' => 'C']); // 취소 
            
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

    public function request_confirm(Request $request){

        $login_user = Auth::user();
        $user_id = $login_user->getId();
        $return = new \stdClass;

        $reservation_id = $request->reservation_id;

        $res_user_id = Reservation::select('user_id')->where('reservations.id',$reservation_id)->first();

        if($res_user_id->user_id == $user_id){
            $res_info = Reservation::join('hotels', 'reservations.hotel_id', '=', 'hotels.id')
                                ->join('rooms', 'reservations.room_id', '=', 'rooms.id')
                                ->join('goods', 'reservations.goods_id', '=', 'goods.id')
                                ->select(   'hotels.type as shop_type',
                                    'reservations.reservation_no as reservation_no', 
                                    'reservations.start_date as start_date', 
                                    'reservations.end_date as end_date', 
                                    'reservations.nights as nights', 
                                    'reservations.peoples as peoples',
                                    'reservations.created_at as created_at',
                                    'reservations.updated_at as updated_at',
                                    'reservations.status as status',
                                    'reservations.name as name',
                                    'reservations.phone as phone',
                                    'reservations.id as reservation_id', 
                                    'reservations.price as reservation_price', 
                                    'rooms.name as room_name',
                                    'goods.goods_name as goods_name', 
                                    'goods.sale_price as sale_price',
                                    'hotels.name as hotel_name',
                                    'hotels.tel as hotel_tel',
                                    'goods.id as goods_id',
                                    DB::raw('(select file_name from goods_images where goods_images.goods_id = goods.id order by order_no asc limit 1 ) as thumb_nail'),
                        )         
                        ->where('reservations.id',$reservation_id)
                        ->where('reservations.user_id', $user_id)
                        ->first();

            $result = Reservation::where('id', $reservation_id)->update(['status' => 'P']); // 입금확인

            $title = "[루밍 입금 확인 요청]";
            $content = $res_info->hotel_name." 담당자님 ".$res_info->name."님이 예약하신 '".$res_info->goods_name."' 상품에 대한 입금 확인을 요청하셨습니다. \n\n예약번호 : ".$res_info->reservation_no."\n"."예약자 : ".$res_info->name."\n"."입금액 : ".number_format($res_info->reservation_price)."원";
    
            $sms = new \stdClass;
            $sms->phone = str_replace('-','',$res_info->hotel_tel);
            $sms->title = $title;
            $sms->content = $content;

            Sms::send($sms);

            $return->status = "200";
            $return->msg = "예약 확인이 요청되었습니다." ;
        }else{
            $return->status = "500";
            $return->reason = "입금확인 요청 권한이 없습니다." ;
        }

        return response()->json($return, 200)->withHeaders([
            'Content-Type' => 'application/json'
        ]);;

    }

    public function confirm(Request $request){

        $login_user = Auth::user();
        $user_id = $login_user->getId();
        $return = new \stdClass;

        $reservation_id = $request->reservation_id;

        $res_info = Reservation::join('hotels', 'reservations.hotel_id', '=', 'hotels.id')
                                ->join('rooms', 'reservations.room_id', '=', 'rooms.id')
                                ->join('goods', 'reservations.goods_id', '=', 'goods.id')
                                ->select(   'hotels.type as shop_type',
                                    'reservations.reservation_no as reservation_no', 
                                    'reservations.start_date as start_date', 
                                    'reservations.end_date as end_date', 
                                    'reservations.nights as nights', 
                                    'reservations.peoples as peoples',
                                    'reservations.created_at as created_at',
                                    'reservations.updated_at as updated_at',
                                    'reservations.status as status',
                                    'reservations.name as name',
                                    'reservations.phone as phone',
                                    'reservations.id as reservation_id', 
                                    'reservations.price as reservation_price', 
                                    'rooms.name as room_name',
                                    'goods.goods_name as goods_name', 
                                    'goods.sale_price as sale_price',
                                    'hotels.name as hotel_name',
                                    'hotels.tel as hotel_tel',
                                    'hotels.id as hotel_id',
                                    'goods.id as goods_id',
                                    DB::raw('(select file_name from goods_images where goods_images.goods_id = goods.id order by order_no asc limit 1 ) as thumb_nail'),
                        )         
                        ->where('reservations.id',$reservation_id)
                        ->first();


        $hotel_info = Hotel::where('id',$res_info->hotel_id)->first();

        if($hotel_info->partner_id == $user_id){
            
            $result = Reservation::where('id', $request->reservation_id)->update(['status' => 'S']); // 예약 확정

            if($result){
                $title = "[루밍 예약 확정안내]";
                $content = $res_info->name."님이 예약하신 '".$res_info->goods_name."' 상품의 예약이 확정되었습니다. \n\n예약번호 : ".$res_info->reservation_no."\n"."예약자 : ".$res_info->name."\n"."입금액 : ".number_format($res_info->reservation_price);
        
                $sms = new \stdClass;
                $sms->phone = str_replace('-','',$res_info->phone);
                $sms->title = $title;
                $sms->content = $content;

                Sms::send($sms);

                $return->status = "200";
                $return->msg = "예약 확인이 요청되었습니다." ;

            }else{
                $return->status = "500";
                $return->msg = "예약 변경에 실패했습니다." ;
            }
            
            
        }else{
            $return->status = "500";
            $return->reason = "입금확인 요청 권한이 없습니다." ;
        }

        return response()->json($return, 200)->withHeaders([
            'Content-Type' => 'application/json'
        ]);;

    }

    



}
