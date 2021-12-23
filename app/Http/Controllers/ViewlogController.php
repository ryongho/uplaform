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

        $return = new \stdClass;

        $result = Viewlog::insertGetId([
            'user_id'=> $request->user_id ,
            'goods_id'=> $request->goods_id ,
            'created_at'=> Carbon::now(),
        ]);

        if($result){ //DB 입력 성공

            $return->status = "200";
            $return->msg = "success";
            $return->insert_id = $result ;
        }else{
            $return->status = "500";
            $return->msg = "fail";
        }

        return response()->json($return, 200)->withHeaders([
            'Content-Type' => 'application/json'
        ]);
    }



}
