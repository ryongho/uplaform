<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Wish;
use Illuminate\Support\Carbon;

class WishController extends Controller
{
    public function regist(Request $request)
    {
        //dd($request);

        Wish::insert([
            'user_id'=> $request->user_id ,
            'goods_id'=> $request->goods_id ,
            'created_at'=> Carbon::now(),
        ]);
    }



}
