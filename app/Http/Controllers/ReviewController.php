<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Review;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function regist(Request $request)
    {
        //dd($request);

        $login_user = Auth::user();
        $user_id = $login_user->getId();
        $user_nickname = $login_user->getNickname();

        $result = Review::insertGetId([
            'user_id'=> $user_id ,
            'nickname'=> $user_nickname,
            'reservation_id'=> $request->reservation_id ,
            'goods_id'=> $request->goods_id ,
            'review'=> $request->review ,
            'grade'=> $request->grade ,
            'created_at'=> Carbon::now(),
        ]);

        $return = new \stdClass;

        if($result){ //DB 입력 성공
            $return->status = "200";
            $return->msg = "success";
            $return->inserted_id = $result;
        }else{
            $return->status = "501";
            $return->msg = "fail";
        }

        return response()->json($return, 200)->withHeaders([
            'Content-Type' => 'application/json'
        ]);;
    
    }

    public function list(Request $request)
    {
        //dd($request);
        $goods_id = $request->goods_id;

        $rows = Review::where('goods_id',$goods_id)->orderBy('created_at','desc')->get();

        $return = new \stdClass;

        $return->status = "200";
        $return->cnt = count($rows);
        $return->data = $rows ;

        return response()->json($return, 200)->withHeaders([
            'Content-Type' => 'application/json'
        ]);;
    }

    public function detail(Request $request){
        $id = $request->id;

        $rows = Review::where('id','=',$id)->get();

        $return = new \stdClass;

        $return->status = "200";
        $return->data = $rows ;
    
        return response()->json($return, 200)->withHeaders([
            'Content-Type' => 'application/json'
        ]);;

    }





}
