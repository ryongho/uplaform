<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Viewlog;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ViewlogController extends Controller
{
    public function regist(Request $request)
    {
        //dd($request);

        $user_id = 0;
        
        if($request->bearerToken() != ""){
            $tokens = explode('|',$request->bearerToken());
            $token_info = DB::table('personal_access_tokens')->where('id',$tokens[0])->first();
            $user_id = $token_info->tokenable_id;
        }

        $return = new \stdClass;

        $result = Viewlog::insertGetId([
            'user_id'=> $user_id ,
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
