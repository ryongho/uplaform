<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\Recommend;
use App\Models\Goods;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class RecommendController extends Controller
{
    public function regist(Request $request)
    {
        
        $return = new \stdClass;        

        $recommend = Recommend::where('order_no',$request->order_no)->count();

        if($recommend){
            Recommend::where('order_no',$request->order_no)->delete();
        }
    
        $result = Recommend::insertGetId([
            'goods_id'=> $request->goods_id ,
            'order_no'=> $request->order_no ,
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


        $rows = Recommend::join('goods', 'recommends.goods_id', '=', 'goods.id')
                        ->select('*',
                            DB::raw('(select file_name from goods_images where goods_images.goods_id = recommends.goods_id order by order_no asc limit 1 ) as thumb_nail'),
                        ) 
                        ->orderBy('order_no','asc')
                        ->get();

        $return = new \stdClass;

        $return->status = "200";
        $return->cnt = count($rows);
        $return->data = $rows ;

        echo(json_encode($return));

    }

    



}
