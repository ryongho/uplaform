<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Goods;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\SmsLog;
use App\Models\Reservation;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class SMSController extends Controller
{
    public function log_regist($result){    
        
        SmsLog::insert([
            'phone'=> $result->phone,
            'status'=> $result->status ,
            'content'=> $result->content ,
            'fail_reason'=> $result->fail_reason ,
            'send_date'=> $result->send_date ,
            'created_at'=> Carbon::now(),
        ]);
    }

    public function send($sms)
    {    
        $_api_url = 'https://message.ppurio.com/api/send_utf8_json.php';     // UTF-8 인코딩과 JSON 응답용 호출 페이지
        
        $_param['userid'] = 'rooming';           // [필수] 뿌리오 아이디
        $_param['callback'] = '01062328507';    // [필수] 발신번호 - 숫자만
        $_param['phone'] = $sms->phone;       // [필수] 수신번호 - 여러명일 경우 |로 구분 '010********|010********|010********'
        $_param['msg'] = $sms->content;   // [필수] 문자내용 - 이름(names)값이 있다면 [*이름*]가 치환되서 발송됨
        //$_param['names'] = '홍길동';            // [선택] 이름 - 여러명일 경우 |로 구분 '홍길동|이순신|김철수'
        //$_param['appdate'] = '20190502093000';  // [선택] 예약발송 (현재시간 기준 10분이후 예약가능)
        $_param['subject'] = '테스트';          // [선택] 제목 (30byte)
        //$_param['file1'] = '@이미지파일경로;type=image/jpg'; // [선택] 포토발송 (jpg, jpeg만 지원  300 K  이하)

        $_curl = curl_init();
        curl_setopt($_curl,CURLOPT_URL,$_api_url);
        curl_setopt($_curl,CURLOPT_POST,true);
        curl_setopt($_curl,CURLOPT_SSL_VERIFYPEER,false);
        curl_setopt($_curl,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($_curl,CURLOPT_POSTFIELDS,$_param);
        $_result = curl_exec($_curl);
        curl_close($_curl);

        $_result = json_decode($_result);
        
        if($_result->result == "ok"){
            $sms->status = "S";
            $sms->send_date = Carbon::now();
            $sms->fail_reason = $_result->result;
            $sms->msgid = $_result->msgid;
            $sms->ok_cnt = $_result->ok_cnt;
            $sms->type = $_result->type;

        }else{
            $sms->status = "F";
            $sms->send_date = Carbon::now();
            $sms->fail_reason = $_result->result;
        }

        $this->log_regist($sms);
    
    }



    public function list(Request $request){
        $s_no = $request->start_no;
        $row = $request->row;

        $orderby = "goods.id";
        $order = "desc";

        if($request->orderby == 'price'){
            $orderby = "goods.price";
            $order = "asc";
        }else if($request->orderby == "distance"){
            $orderby = "distance";
            $order = "asc";
        }

        $rows = Goods::join('hotels', 'goods.hotel_id', '=', 'hotels.id')
                        ->join('rooms', 'goods.room_id', '=', 'rooms.id')
                        ->select(   'hotels.type as shop_type', 
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
                                    Hotel::raw('(6371 * acos( cos( radians('.$request->target_latitude.') ) * cos( radians( hotels.latitude ) ) * cos( radians( hotels.longtitude ) - radians('.$request->target_longtitude.') ) + sin( radians('.$request->target_latitude.') ) * sin( radians( hotels.latitude ) ) ) ) as distance'),
                                    DB::raw('(select count(*) from wishes where goods.id = wishes.goods_id ) as wished '),
                                    DB::raw('(select file_name from goods_images where goods_images.goods_id = goods.id order by order_no asc limit 1 ) as thumb_nail'),
                                    DB::raw('(select avg(grade) from reviews where reviews.goods_id = goods.id) as grade'),
                                    DB::raw('(select count(grade) from reviews where reviews.goods_id = goods.id) as grade_cnt'),
                        )         
                        ->where('goods.id','>=',$s_no)
                        ->whereBetween('hotels.latitude', [$request->a_latitude, $request->b_latitude])
                        ->whereBetween('hotels.longtitude', [$request->a_longtitude, $request->b_longtitude])
                        ->where('start_date' ,"<=", $request->start_date)
                        ->where('end_date' ,">=", $request->end_date)
                        ->orderBy($orderby, $order)
                        ->limit($row)->get();

        $return = new \stdClass;

        $return->status = "200";
        $return->cnt = count($rows);
        $return->data = $rows ;

        echo(json_encode($return));

    }

    public function list_by_hotel(Request $request){
        $hotel_id = $request->hotel_id;
       
        $rows = Goods::join('hotels', 'goods.hotel_id', '=', 'hotels.id')
                        ->join('rooms', 'goods.room_id', '=', 'rooms.id')
                        ->select(   'hotels.type as shop_type', 
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
                                    'hotels.id as hotel_id',
                                    'rooms.id as room_id',
                                    DB::raw('(select file_name from goods_images where goods_images.goods_id = goods.id order by order_no asc limit 1 ) as thumb_nail'),
                                    DB::raw('(select avg(grade) from reviews where reviews.goods_id = goods.id) as grade'),
                                    DB::raw('(select count(grade) from reviews where reviews.goods_id = goods.id) as grade_cnt'),
                        )         
                        ->where('hotels.id','=',$hotel_id)
                        ->orderBy('sale_price', 'asc')
                        ->get();

        $return = new \stdClass;

        $return->status = "200";
        $return->cnt = count($rows);
        $return->data = $rows ;

        echo(json_encode($return));

    }

    public function list_by_partner(Request $request){

        $login_user = Auth::user();

        $rows = Goods::join('hotels', 'goods.hotel_id', '=', 'hotels.id')
                        ->join('rooms', 'goods.room_id', '=', 'rooms.id')
                        ->select(   'hotels.type as shop_type', 
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
                                    'goods.options as options',
                                    'goods.amount as amount',
                                    DB::raw('(select file_name from goods_images where goods_images.goods_id = goods.id order by order_no asc limit 1 ) as thumb_nail'),
                        )         
                        ->where('hotels.partner_id','=',$login_user->id)
                        ->orderBy('goods.id', 'desc')
                        ->get();


        $return = new \stdClass;

        $return->status = "200";
        $return->cnt = count($rows);
        $return->data = $rows ;

        echo(json_encode($return));

    }

    

    public function detail(Request $request){
        $id = $request->id;

        $rows = Goods::join('hotels', 'goods.hotel_id', '=', 'hotels.id')
                ->join('rooms', 'goods.room_id', '=', 'rooms.id')
                ->select(   'hotels.type as shop_type', 
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
                'hotels.id as hotel_id',
                'rooms.id as room_id',
                'goods.options as options',
                'goods.amount as amount',
                'goods.start_date as start_date',
                'goods.end_date as end_date',
                DB::raw('(select count(*) from wishes where goods.id = wishes.goods_id ) as wished '),
                )
                ->where('goods.id','=',$id)->get();

        $grade = Review::where('goods_id','=',$id)->whereNotNull('grade')->avg('grade');
        
        $images = GoodsImage::where('goods_id','=',$id)->orderBy('order_no')->get();

        $return = new \stdClass;

        $return->status = "200";
        $rows[0]->grade = $grade;
        $return->data = $rows ;
        $return->images = $images ;
        
        echo(json_encode($return));

    }



}
