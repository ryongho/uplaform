<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Goods;
use Illuminate\Support\Carbon;

class GoodsController extends Controller
{
    public function regist(Request $request)
    {
        //dd($request);

        Goods::insert([
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
            'created_at'=> Carbon::now(),
        ]);
    }



}
