<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Push;
use App\Models\Goods;
use App\Models\Hotel;
use App\Models\Room;
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

        echo(json_encode($return));
    }

    
    public function list(){

        $return = new \stdClass;

        $login_user = Auth::user();
        $user_id = $login_user->getId();

        
        $rows = Push::whereRaw('(type = "A") or (type = "P" and target_user = "'.$user_id.'")')
                        //->whereOr('target_user','=',$user_id)
                        ->orderBy('send_date', 'desc')
                        ->get();

        
        $return->status = "200";
        $return->cnt = count($rows);
        $return->data = $rows;

        echo(json_encode($return));
        
    }





}
