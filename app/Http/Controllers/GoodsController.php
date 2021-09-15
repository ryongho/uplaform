<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Goods;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\Hotel;
use App\Models\GoodsImage;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class GoodsController extends Controller
{
    public function regist(Request $request)
    {
        //dd($request);

        //dd($request);
        $return = new \stdClass;

        $return->status = "500";
        $return->msg = "관리자에게 문의";
        $return->data = $request->name ;

        $login_user = Auth::user();
        $user_id = $login_user->getId();
        $user_type = $login_user->getType();

        $cnt = Hotel::where('partner_id',$user_id)->where('id',$request->hotel_id)->count();
        
        if($cnt == 0 || $user_id == ""){// 아이디 존재여부
            $return->status = "601";
            $return->msg = "해당 호텔에 상품을 등록 할 수 없는 계정입니다.";
            $return->data = $request->name ;
        }elseif( $user_type == 0 ){//일반회원
            $return->status = "602";
            $return->msg = "일반 회원입니다.";
            $return->data = $request->name ;
        }else{
            
            $result = Goods::insertGetId([
                'hotel_id'=> $request->hotel_id ,
                'room_id'=> $request->room_id ,
                'goods_name'=> $request->goods_name ,
                'start_date'=> $request->start_date ,
                'end_date'=> $request->end_date ,
                'nights'=> $request->nights ,
                'options'=> $request->options ,
                'type'=> $request->type ,
                'price'=> $request->price ,
                'sale_price'=> $request->sale_price ,
                'amount'=> $request->amount ,
                'min_nights'=> $request->min_nights ,
                'max_nights'=> $request->max_nights ,
                'breakfast'=> $request->breakfast ,
                'parking'=> $request->parking ,
                'created_at'=> Carbon::now(),
            ]);

            if($result){ //DB 입력 성공

                $no = 1; 

                $images = explode(",",$request->images);
                foreach( $images as $image){
                
                    $result_img = GoodsImage::insertGetId([
                        'goods_id'=> $result ,
                        'file_name'=> $image ,
                        'order_no'=> $no ,
                        'created_at' => Carbon::now()
                    ]);

                    $no++;
                }

                $return->status = "200";
                $return->msg = "success";
                $return->insert_id = $result ;
            }
        }

        echo(json_encode($return));
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
                                    DB::raw('(select file_name from goods_images where goods_images.goods_id = goods.id order by order_no asc limit 1 ) as thumb_nail'),
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
                                    DB::raw('(select file_name from goods_images where goods_images.goods_id = goods.id order by order_no asc limit 1 ) as thumb_nail'),
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
                )
                ->where('goods.id','=',$id)->get();

        $images = GoodsImage::where('goods_id','=',$id)->orderBy('order_no')->get();

        $return = new \stdClass;

        $return->status = "200";
        $return->data = $rows ;
        $return->images = $images ;

        echo(json_encode($return));

    }



}
