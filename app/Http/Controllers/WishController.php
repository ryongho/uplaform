<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Wish;
use App\Models\Goods;
use App\Models\Hotel;
use App\Models\Room;
use App\Models\Comparaison;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WishController extends Controller
{
    public function regist(Request $request)
    {
        //dd($request);

        $login_user = Auth::user();
        $user_id = $login_user->getId();

        Wish::insert([
            'user_id'=> $user_id ,
            'goods_id'=> $request->goods_id ,
            'created_at'=> Carbon::now(),
        ]);
    }

    public function toggle(Request $request)
    {
        //dd($request);
        $return = new \stdClass;

        $login_user = Auth::user();
        $user_id = $login_user->getId();

        $cnt = Wish::where('goods_id',$request->goods_id)->where('user_id',$user_id)->count();

        if($cnt){
            
            Wish::where('goods_id',$request->goods_id)->where('user_id',$user_id)->delete();
            $return->status = "200";
            $return->added = 'N';

        }else{
            Wish::insert([
                'user_id'=> $user_id ,
                'goods_id'=> $request->goods_id ,
                'created_at'=> Carbon::now(),
            ]);

            $return->status = "200";
            $return->added = 'Y';

            
        }


        echo(json_encode($return));
        
    }

    public function list(){

        $return = new \stdClass;

        $login_user = Auth::user();
        $user_id = $login_user->getId();

        $rows = Goods::join('hotels', 'goods.hotel_id', '=', 'hotels.id')
                        ->join('rooms', 'goods.room_id', '=', 'rooms.id')
                        ->join('wishes', 'goods.id', '=', 'wishes.goods_id')
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
                        ->where('wishes.user_id','=',$user_id)
                        //->where('start_date' ,"<=", Carbon::now())
                        //->where('end_date' ,">=", Carbon::now())
                        ->orderBy('wishes.id', 'desc')
                        ->get();


        $return->status = "200";
        $return->cnt = count($rows);
        $return->data = $rows;

        echo(json_encode($return));
        
    }

    public function compare(Request $request){

        $login_user = Auth::user();
        $user_id = $login_user->getId();

        $inserted_id = Comparaison::insertGetId([
            'user_id'=> $user_id ,
            'goods_id_1'=> $request->goods_id_1 ,
            'goods_id_2'=> $request->goods_id_2 ,
            'goods_id_3'=> $request->goods_id_3 ,
            'goods_id_4'=> $request->goods_id_4 ,
            'goods_id_5'=> $request->goods_id_5 ,  
            'created_at'=> Carbon::now(),
        ]);

        $return = new \stdClass;

        $return->status = "200";
        $return->inserted_id = $inserted_id;
        echo(json_encode($return));

    }



}
