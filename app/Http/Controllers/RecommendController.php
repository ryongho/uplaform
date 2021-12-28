<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\Recommend;
use App\Models\Goods;
use App\Models\Hotel;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class RecommendController extends Controller
{
    public function regist(Request $request)
    {
        
        $return = new \stdClass;        

        $recommend = Recommend::where('order_no',$request->order_no)->count();

        if($recommend){
            Recommend::where('order_no',$request->order_no)->delete();
        }
    
        $result = Recommend::insertGetId([
            'goods_id'=> $request->goods_id ,
            'order_no'=> $request->order_no ,
            'created_at'=> Carbon::now(),
        ]);

        if($result){ //DB 입력 성공
            $return->status = "200";
            $return->msg = "success";
        }else{
            $return->status = "501";
            $return->msg = "fail";
        }
        

        return response()->json($return, 200)->withHeaders([
            'Content-Type' => 'application/json'
        ]);;
    }

    public function list(Request $request){


        $rows = Recommend::join('goods', 'recommends.goods_id', '=', 'goods.id')
                        ->join('rooms', 'goods.room_id', '=', 'rooms.id')
                        ->join('hotels', 'goods.hotel_id', '=', 'hotels.id')
                        ->select(   'hotels.type as shop_type', 
                                    'rooms.name as room_name',
                                    'hotels.name as hotel_name',
                                    'goods.goods_name as goods_name', 
                                    'goods.price as price',
                                    'hotels.address as address',
                                    'goods.sale_price as sale_price',
                                    'goods.checkin as checkin',
                                    'goods.checkout as checkout',
                                    'goods.breakfast as breakfast',
                                    'hotels.parking as parking',
                                    'hotels.latitude as latitude',
                                    'hotels.longtitude as longtitude',
                                    'goods.id as goods_id',
                                    'goods.sale as sale',
                                    DB::raw('(select file_name from goods_images where goods_images.goods_id = goods.id order by order_no asc limit 1 ) as thumb_nail'),
                                    DB::raw('(select avg(grade) from reviews where reviews.goods_id = goods.id) as grade'),
                                    DB::raw('(select count(grade) from reviews where reviews.goods_id = goods.id) as grade_cnt'),
                        )  
                        ->orderBy('order_no','asc')
                        ->get();


        $return = new \stdClass;

        $return->status = "200";
        $return->cnt = count($rows);
        $return->data = $rows ;

        return response()->json($return, 200)->withHeaders([
            'Content-Type' => 'application/json'
        ]);;

    }

    



}
