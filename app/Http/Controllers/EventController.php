<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\Event;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class EventController extends Controller
{
    public function regist(Request $request)
    {
        
        $return = new \stdClass;   

        $login_user = Auth::user();
        

        $result = Event::insertGetId([
            'title'=> $request->title ,
            'content'=> $request->content ,
            'writer'=> $login_user->getId(),
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

    public function list(Request $request){


        $rows = Event::select(DB::raw('*','(select nickname from users where notices.writer = users.id order by order_no asc limit 1 ) as writer'))->get();

        $return = new \stdClass;

        $return->status = "200";
        $return->cnt = count($rows);
        $return->data = $rows ;

        echo(json_encode($return));

    }

    public function detail(Request $request){
        $id = $request->id;

        $rows = Event::where('id','=',$id)->get();

        $return = new \stdClass;

        $return->status = "200";
        $return->data = $rows ;

        echo(json_encode($return));

    }

    



}
