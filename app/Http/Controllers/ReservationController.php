<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\Goods;
use App\Models\Hotel;
use App\Models\Reservation;
use App\Models\Push;
use App\Http\Controllers\SMSController;
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

        if($result){ //DB 입력 성공

            $return->status = "200";
            $return->msg = "success";
            $return->insert_id = $result ;

            Goods::where('id',$request->goods_id)->update(['amount' => $goods->amount-1]);

            $content = $request->name."님 ".$goods->name." 상품이 예약 되었습니다.";

            $sms = new \stdClass;
            $sms->phone = $request->phone;
            $sms->content = $content;

            //$smsController = new SMScontroller();
            //$smsController->send($sms);
            //SMScontroller::send($sms);

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
            $return->insert_id = $result ;
        }

        echo(json_encode($return));
    }

    public function list(Request $request){
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
        }else if($reservation_info->status == "C" || $reservation_info->status == "X"){
            $return->status = "602";
            $return->msg = "이미 취소 처리된 예약입니다.";
            $return->reservation_id = $request->id;
        }else{
            $result = Reservation::where('id', $request->id)->where('user_id',$user_id)->update(['status' => 'X']);

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

    



}
