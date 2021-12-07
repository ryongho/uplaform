<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\Goods;
use App\Models\Hotel;
use App\Models\Reservation;
use App\Models\Push;
use App\Models\Sms;
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
        $user_type = $login_user->getType();

        $now = date('ymdHis');
        
        $reservation_no = "R_".$now."_".$request->goods_id."_".$user_id;

        $goods = Goods::where('id',$request->goods_id)->first();
        if(isset($goods)){
            $result = Reservation::insertGetId([
                'user_id'=> $user_id ,
                'reservation_no'=> $reservation_no ,
                'hotel_id'=> $goods->hotel_id ,
                'room_id'=> $goods->room_id ,
                'goods_id'=> $request->goods_id ,
                'start_date'=> $request->start_date ,
                'end_date'=> $request->end_date ,
                'nights'=> $request->nights ,
                'price'=> $goods->price ,
                'peoples'=> $request->peoples ,
                'name'=> $request->name ,
                'phone'=> $request->phone ,
                'visit_way'=> $request->visit_way ,
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

            Goods::where('id',$request->goods_id)->update(['amount' => $goods->amount-1]);

            $reservation = Reservation::where('id',$result)->first();

            $pay_info = new \stdClass;

            $pay_info->goods_name = $goods->goods_name;
            $pay_info->name = $request->name;
            $pay_info->reservation_no = $reservation->reservation_no;
            $pay_info->price = $goods->price;
            $now = Carbon::now();

            $pay_info->expire = $now->addMinute(30)->format('Ymd');

            $hotel_info = Hotel::where('id',$goods->hotel_id)->first();

            $title = "[루밍 예약 입금안내]";
            $content = $request->name."님 아래 계좌로 입금해주시면 담당자 확인 후에 예약이 완료 됩니다.\n\n입금계좌 : \n ".$hotel_info->account_number." ".$hotel_info->bank_name." (예금주 : ".$hotel_info->account_name.")";

            $sms = new \stdClass;
            $sms->phone = $request->phone;
            $sms->content = $content;
            $sms->title = $title;

            Sms::send($sms);

            $result = Push::insert([
                'user_id'=> 1,
                'content'=> $content ,
                'type'=> "R" ,
                'target_user' => $user_id ,
                'target_id' => $result ,
                'send_date'=> Carbon::now() ,
                'created_at'=> Carbon::now(),
            ]);
            

            
        }else{
            $return->status = "500";
            $return->msg = "fail";
        }

        echo(json_encode($return));
    }

    public function list(Request $request){

        header("Access-Control-Allow-Origin: *");

        $s_no = $request->start_no;
        $row = $request->row;

        $orderby = "reservations.created_at";
        $order = "desc";

        /*if($request->orderby == 'price'){
            $orderby = ".price";
            $order = "asc";
        }else if($request->orderby == "distance"){
            $orderby = "distance";
            $order = "asc";
        }*/

        $rows = Reservation::join('hotels', 'reservations.hotel_id', '=', 'hotels.id')
                                ->join('rooms', 'reservations.room_id', '=', 'rooms.id')
                                ->join('goods', 'reservations.goods_id', '=', 'goods.id')
                                ->select(   
                                    'reservations.reservation_no as reservation_no', 
                                    'reservations.start_date as start_date', 
                                    'reservations.end_date as end_date', 
                                    'reservations.nights as nights', 
                                    'reservations.peoples as peoples', 
                                    'reservations.created_at as created_at',
                                    'reservations.updated_at as updated_at',
                                    'reservations.status as status',
                                    'reservations.name as name',
                                    'reservations.visit_way as visit_way',
                                    'reservations.phone as phone',
                                    'reservations.id as reservation_id',
                                    'hotels.type as shop_type', 
                                    'rooms.name as room_name',
                                    'hotels.name as hotel_name',
                                    'goods.goods_name as goods_name', 
                                    'goods.price as price',
                                    'hotels.address as address',
                                    'goods.sale_price as sale_price',
                                    'rooms.checkin as checkin',
                                    'rooms.checkout as checkout',
                                    'goods.breakfast as breakfast',
                                    'hotels.parking as parking',
                                    'hotels.latitude as latitude',
                                    'hotels.longtitude as longtitude',
                                    'goods.id as goods_id',
                                    DB::raw('(select file_name from goods_images where goods_images.goods_id = goods.id order by order_no asc limit 1 ) as thumb_nail'),
                        )         
                        ->where('reservations.start_date' ,">=", $request->start_date)
                        ->where('reservations.end_date' ,"<=", $request->end_date)
                        ->orderBy($orderby, $order)
                        ->limit($row)->get();

        $return = new \stdClass;

        $return->status = "200";
        $return->cnt = count($rows);
        $return->data = $rows ;

        echo(json_encode($return));

    }

    public function list_by_hotel(Request $request){
        $s_no = $request->start_no;
        $row = $request->row;

        $login_user = Auth::user();
        $user_id = $login_user->getId();

        $hotel_info = Hotel::where('partner_id',$user_id)->first();
        
        $orderby = "reservations.created_at";
        $order = "desc";

        $rows = Reservation::join('hotels', 'reservations.hotel_id', '=', 'hotels.id')
                                ->join('rooms', 'reservations.room_id', '=', 'rooms.id')
                                ->join('goods', 'reservations.goods_id', '=', 'goods.id')
                                ->select(   
                                    'reservations.reservation_no as reservation_no', 
                                    'reservations.start_date as start_date', 
                                    'reservations.end_date as end_date', 
                                    'reservations.nights as nights', 
                                    'reservations.peoples as peoples', 
                                    'reservations.created_at as created_at',
                                    'reservations.updated_at as updated_at',
                                    'reservations.status as status',
                                    'reservations.name as name',
                                    'reservations.visit_way as visit_way',
                                    'reservations.phone as phone',
                                    'reservations.id as reservation_id',
                                    'hotels.type as shop_type', 
                                    'rooms.name as room_name',
                                    'hotels.name as hotel_name',
                                    'goods.goods_name as goods_name', 
                                    'goods.price as price',
                                    'hotels.address as address',
                                    'goods.sale_price as sale_price',
                                    'rooms.checkin as checkin',
                                    'rooms.checkout as checkout',
                                    'goods.breakfast as breakfast',
                                    'hotels.parking as parking',
                                    'hotels.latitude as latitude',
                                    'hotels.longtitude as longtitude',
                                    'goods.id as goods_id',
                                    DB::raw('(select file_name from goods_images where goods_images.goods_id = goods.id order by order_no asc limit 1 ) as thumb_nail'),
                        )         
                        ->where('reservations.start_date' ,">=", $request->start_date)
                        ->where('reservations.end_date' ,"<=", $request->end_date)
                        ->where('reservations.hotel_id','=',$hotel_info->id)
                        ->orderBy($orderby, $order)
                        ->limit($row)->get();

        $return = new \stdClass;

        $return->status = "200";
        $return->cnt = count($rows);
        $return->data = $rows ;

        echo(json_encode($return));

    }

    public function list_by_user(Request $request){

        $login_user = Auth::user();
        $user_id = $login_user->getId();

        $orderby = "reservations.created_at";
        $order = "desc";
    
       
        $rows = Reservation::join('hotels', 'reservations.hotel_id', '=', 'hotels.id')
                                ->join('rooms', 'reservations.room_id', '=', 'rooms.id')
                                ->join('goods', 'reservations.goods_id', '=', 'goods.id')
                                ->leftJoin('reviews', 'reservations.id', '=', 'reviews.reservation_id')
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
                                    'reservations.visit_way as visit_way',
                                    'reservations.phone as phone', 
                                    'reservations.id as reservation_id', 
                                    'rooms.name as room_name',
                                    'hotels.name as hotel_name',
                                    'goods.goods_name as goods_name', 
                                    'goods.price as price',
                                    'hotels.address as address',
                                    'goods.sale_price as sale_price',
                                    'rooms.checkin as checkin',
                                    'rooms.checkout as checkout',
                                    'goods.breakfast as breakfast',
                                    'hotels.parking as parking',
                                    'hotels.latitude as latitude',
                                    'hotels.longtitude as longtitude',
                                    'goods.id as goods_id',
                                    DB::raw('(select file_name from goods_images where goods_images.goods_id = goods.id order by order_no asc limit 1 ) as thumb_nail'),
                                    'reviews.id as review_id',
                                    'reviews.review as review',
                                    'reviews.created_at as review_created_at',
                                    'reviews.nickname as review_nickname',
                                    'reviews.grade as review_grade',
                        )         
                        ->where('reservations.user_id',$user_id)
                        ->orderBy($orderby, $order)
                        ->get();

        $return = new \stdClass;

        $return->status = "200";
        $return->cnt = count($rows);
        $return->data = $rows ;

        echo(json_encode($return));

    }

    public function list_by_goods(Request $request){
        $goods_id = $request->goods_id;

        $orderby = "reservations.created_at";
        $order = "desc";

        $rows = Reservation::join('hotels', 'reservations.hotel_id', '=', 'hotels.id')
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
                                    'reservations.visit_way as visit_way',
                                    'reservations.phone as phone',
                                    'reservations.id as reservation_id',
                                    'rooms.name as room_name',
                                    'hotels.name as hotel_name',
                                    'goods.goods_name as goods_name', 
                                    'goods.price as price',
                                    'hotels.address as address',
                                    'goods.sale_price as sale_price',
                                    'rooms.checkin as checkin',
                                    'rooms.checkout as checkout',
                                    'goods.breakfast as breakfast',
                                    'hotels.parking as parking',
                                    'hotels.latitude as latitude',
                                    'hotels.longtitude as longtitude',
                                    'goods.id as goods_id',
                                    DB::raw('(select file_name from goods_images where goods_images.goods_id = goods.id order by order_no asc limit 1 ) as thumb_nail'),
                        )         
                        ->where('goods.id',$goods_id)
                        ->orderBy($orderby, $order)
                        ->get();


        $return = new \stdClass;

        $return->status = "200";
        $return->cnt = count($rows);
        $return->data = $rows ;

        echo(json_encode($return));

    }

    

    public function detail(Request $request){
        $id = $request->id;

        $orderby = "reservations.created_at";
        $order = "desc";

        $rows = Reservation::join('hotels', 'reservations.hotel_id', '=', 'hotels.id')
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
                                    'reservations.visit_way as visit_way',
                                    'reservations.phone as phone',
                                    'reservations.id as reservation_id', 
                                    'rooms.name as room_name',
                                    'hotels.name as hotel_name',
                                    'goods.goods_name as goods_name', 
                                    'goods.price as price',
                                    'hotels.address as address',
                                    'goods.sale_price as sale_price',
                                    'rooms.checkin as checkin',
                                    'rooms.checkout as checkout',
                                    'goods.breakfast as breakfast',
                                    'hotels.parking as parking',
                                    'hotels.latitude as latitude',
                                    'hotels.longtitude as longtitude',
                                    'goods.id as goods_id',
                                    DB::raw('(select file_name from goods_images where goods_images.goods_id = goods.id order by order_no asc limit 1 ) as thumb_nail'),
                        )         
                        ->where('reservations.id',$id)
                        ->orderBy($orderby, $order)
                        ->get();

        $return = new \stdClass;

        $return->status = "200";
        $return->data = $rows ;

        echo(json_encode($return));

    }

    public function cancel(Request $request){
        //dd($request);
        $return = new \stdClass;
        $login_user = Auth::user();

        $return->status = "200";
        $return->msg = "취소 등록";
        //$return->id = $request->id;

        $user_id = $login_user->id;

        $reservation_info = Reservation::where('id', $request->id)->where('user_id',$user_id)->first();
        if(!$reservation_info){
            $return->status = "601";
            $return->msg = "유효한 예약 정보가 아닙니다.";
            $return->reservation_id = $request->id;
        }else if($reservation_info->status == "C"){
            $return->status = "602";
            $return->msg = "이미 취소 처리된 예약입니다.";
            $return->reservation_id = $request->id;
        }else{
            if($reservation_info->status == "W"){ // 예약 대기 상태인 경우 
                $result = Reservation::where('id', $request->id)->where('user_id',$user_id)->update(['status' => 'C']); // 취소 확정
            }else{//입금 완료 상태
                $result = Reservation::where('id', $request->id)->where('user_id',$user_id)->update(['status' => 'X']);// 취소 신청 - 관리자 확인후 취소 가능

                $title = "[예약 취소 신청 안내]";
                $content = $reservation_info->name."님의 예약이 취소 요청 되었습니다. \n\n 담당자 확인 후 취소 처리 예정입니다. \n\n 예약번호 : ".$reservation_info->reservation_no."\n"."예약자 : ".$reservation_info->name;
        
                $sms = new \stdClass;
                $sms->phone = str_replace('-','',$reservation_info->phone);
                $sms->title = $title;
                $sms->content = $content;

                Sms::send($sms);
            }
            
            if(!$result){
                $return->status = "500";
                $return->msg = "변경 실패";
            }
        }

    
        echo(json_encode($return));

    }

    public function cancel_by_partner(Request $request){
        
        $return = new \stdClass;
        $login_user = Auth::user();

        $return->status = "200";
        $return->msg = "취소 등록";
    
        $user_id = $login_user->id;
        

        $reservation_info = Reservation::where('id', $request->id)->first();
        $hotel_info = Hotel::where('id',$reservation_info->hotel_id)->where('partner_id',$user_id)->first();

        if(!$hotel_info ){
            $return->status = "601";
            $return->msg = "유효한 예약 정보가 아닙니다.";
            $return->reservation_id = $request->id;
        }else if($reservation_info->status == "C"){
            $return->status = "602";
            $return->msg = "이미 취소 처리된 예약입니다.";
            $return->reservation_id = $request->id;
        }else{
            
            $result = Reservation::where('id', $request->id)->update(['status' => 'C']);// 취소 신청 - 관리자 확인후 취소 가능

            $title = "[예약 취소 확정 안내]";
            $content = $reservation_info->name."님 예약 취소 확정 되었습니다. \n\n 예약번호 : ".$reservation_info->reservation_no."\n"."예약자 : ".$reservation_info->name;
    
            $sms = new \stdClass;
            $sms->phone = str_replace('-','',$reservation_info->phone);
            $sms->title = $title;
            $sms->content = $content;

            Sms::send($sms);
            
            if(!$result){
                $return->status = "500";
                $return->msg = "변경 실패";
            }
        }

    
        echo(json_encode($return));

    }

    public function list_cancel(Request $request){

        $login_user = Auth::user();
        $user_id = $login_user->getId();

        $orderby = "reservations.created_at";
        $order = "desc";
    
       
        $rows = Reservation::join('hotels', 'reservations.hotel_id', '=', 'hotels.id')
                                ->join('rooms', 'reservations.room_id', '=', 'rooms.id')
                                ->join('goods', 'reservations.goods_id', '=', 'goods.id')
                                ->leftJoin('reviews', 'reservations.id', '=', 'reviews.reservation_id')
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
                                    'reservations.visit_way as visit_way',
                                    'reservations.phone as phone', 
                                    'reservations.id as reservation_id', 
                                    'rooms.name as room_name',
                                    'hotels.name as hotel_name',
                                    'goods.goods_name as goods_name', 
                                    'goods.price as price',
                                    'hotels.address as address',
                                    'goods.sale_price as sale_price',
                                    'rooms.checkin as checkin',
                                    'rooms.checkout as checkout',
                                    'goods.breakfast as breakfast',
                                    'hotels.parking as parking',
                                    'hotels.latitude as latitude',
                                    'hotels.longtitude as longtitude',
                                    'goods.id as goods_id',
                                    DB::raw('(select file_name from goods_images where goods_images.goods_id = goods.id order by order_no asc limit 1 ) as thumb_nail'),
                                    'reviews.id as review_id',
                                    'reviews.review as review',
                                    'reviews.created_at as review_created_at',
                                    'reviews.nickname as review_nickname',
                                    'reviews.grade as review_grade',
                        )         
                        ->Where('reservations.user_id',$user_id)
                        ->Where('reservations.status','X')
                        ->orWhere('reservations.status','C')
                        ->orderBy($orderby, $order)
                        ->get();

        $return = new \stdClass;

        $return->status = "200";
        $return->cnt = count($rows);
        $return->data = $rows ;

        echo(json_encode($return));

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

        echo(json_encode($return));

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
            $content = $res_info->hotel_name." 담당자님 ".$res_info->name."님이 예약하신 '".$res_info->goods_name."' 상품에 대한 입금 확인을 요청하셨습니다. \n\n예약번호 : ".$res_info->reservation_no."\n"."예약자 : ".$res_info->name."\n"."입금액 : ".number_format($res_info->reservation_price);
    
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

        echo(json_encode($return));

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

        echo(json_encode($return));

    }

    



}
