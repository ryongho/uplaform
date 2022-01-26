<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Push;
use App\Models\Comparaison;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PushController extends Controller
{
    public function regist(Request $request)
    {
        //dd($request);

        $return = new \stdClass;

        $login_user = Auth::user();
        $user_id = $login_user->getId();

        $result = Push::insert([
            'user_id'=> $user_id ,
            'content'=> $request->content  ,
            'type'=> $request->type ,
            'target_user' => $request->target_user,
            'target_id' => $request->id,
            'send_date'=> $request->send_date  ,
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

    
    public function list(){

        $return = new \stdClass;

        $login_user = Auth::user();
        $user_id = $login_user->getId();

        
        $rows = Push::whereRaw('(type != "P" && type != "R") or (target_user = "'.$user_id.'")')
                        //->whereOr('target_user','=',$user_id)
                        ->orderBy('send_date', 'desc')
                        ->get();

        
        $return->status = "200";
        $return->cnt = count($rows);
        $return->data = $rows;

        return response()->json($return, 200)->withHeaders([
            'Content-Type' => 'application/json'
        ]);;
        
    }





}
