<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Push;
use App\Models\User;
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
            if($request->type == "P"){
                
                $tokens = User::select('fcm_token')->where('id',$request->target_user)->get();

                if(count($tokens)){
                    $push = new \stdClass;

                    $push->token = array($tokens[0]['fcm_token']);
                    $push->title = "";
                    $push->body = $request->content;
                    
                    $rows = Push::send_push($push);
                }
            }
            
            
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

    public function test(){

        $return = new \stdClass;
        $push = new \stdClass;
        $tokens = array();
        $tokens[0] = "f8jSNWyrzUVtsjKMWb1QvC:APA91bEDekZtnnA7sQxiCdbgYm_bcHucseRktYHX0g_fa-YeJBSVwSFgkBGvPaE67449CQuNy3zPQxV8enOo3zl845JHHfYmKE6niFfLbM24vQu0HzobYuSIj9wuUhFFsdqnlUsmt_b5";
        $push->token = $tokens;
        $push->title = "test";
        $push->body = "test_body";
        
        $rows = Push::send_push($push);
        
        $return->status = "200";
        $return->data = $rows;

        return response()->json($return, 200)->withHeaders([
            'Content-Type' => 'application/json'
        ]);;
        
    }

    





}
