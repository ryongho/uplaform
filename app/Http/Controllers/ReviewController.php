<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Review;
use Illuminate\Support\Carbon;

class ReviewController extends Controller
{
    public function regist(Request $request)
    {
        //dd($request);

        Review::insert([
            'user_id'=> $request->user_id ,
            'goods_id'=> $request->goods_id ,
            'title'=> $request->title ,
            'review'=> $request->review ,
            'created_at'=> Carbon::now(),
        ]);
    }



}
