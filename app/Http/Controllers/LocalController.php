<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\Local;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class LocalController extends Controller
{
    public function regist(Request $request)
    {
        
        $return = new \stdClass;        

        $recommend = Local::where('order_no',$request->order_no)->count();

        if($recommend){
            Local::where('order_no',$request->order_no)->delete();
        }
    
        $result = Local::insertGetId([
            'name'=> $request->name ,
            'latitude'=> $request->latitude ,
            'longtitude'=> $request->longtitude ,
            'order_no'=> $request->order_no ,
            'img_src'=> $request->img_src ,
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

    public function list(Request $request){


        $rows = Local::orderBy('order_no','asc')
                        ->get();

        $return = new \stdClass;

        $return->status = "200";
        $return->cnt = count($rows);
        $return->data = $rows ;

        return response()->json($return, 200)->withHeaders([
            'Content-Type' => 'application/json'
        ]);;

    }

    



}
