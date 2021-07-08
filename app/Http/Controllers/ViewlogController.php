<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Viewlog;
use Illuminate\Support\Carbon;

class ViewlogController extends Controller
{
    public function regist(Request $request)
    {
        //dd($request);

        Viewlog::insert([
            'user_id'=> $request->user_id ,
            'goods_id'=> $request->goods_id ,
            'created_at'=> Carbon::now(),
        ]);
    }



}
