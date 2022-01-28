<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Qna;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class QnaController extends Controller
{
    

    public function regist(Request $request)
    {
        //dd($request);
        $return = new \stdClass;

        $login_user = Auth::user();
        $user_id = $login_user->getId();

        
        Qna::insert([
            'user_id'=> $user_id ,
            'type'=> $request->type,
            'title'=> $request->title ,
            'content'=> $request->content ,
            'file_src'=> $request->file_src ,
            'status'=> "W" ,
            'created_at'=> Carbon::now(),
        ]);

        $return->status = "200";
        $return->added = 'Y';

        return response()->json($return, 200)->withHeaders([
            'Content-Type' => 'application/json'
        ]);

        
    }

    public function answer(Request $request)
    {
        $return = new \stdClass;
        
        $qna_id = $request->qna_id;

        $result = Qna::where('id', $qna_id)->update([
            'answer_title' => $request->answer_title,
            'answer' => $request->answer,
            'answered_at' => Carbon::now(),
            'status'=> "S" ,                 
        ]);

        if($result){
            $return->status = "200";
            $return->msg = "success";
        }else{
            $return->status = "500";
            $return->msg = "fail";
        }
        
        return response()->json($return, 200)->withHeaders([
            'Content-Type' => 'application/json'
        ]);

        
    }

    public function list(Request $request){

        $return = new \stdClass;

        $login_user = Auth::user();
        $user_id = $login_user->getId();
         
        $rows = Qna::select('id as qna_id','title','content','type','status','created_at','answered_at') 
                    ->where('user_id',$user_id)
                    ->orderby('created_at','desc')
                    ->get();


        $return->status = "200";
        $return->cnt = count($rows);
        $return->data = $rows;

        return response()->json($return, 200)->withHeaders([
            'Content-Type' => 'application/json'
        ]);

        
    }

    public function detail(Request $request){

        $return = new \stdClass;

        $qna_id = $request->qna_id;
         
        $rows = Qna::select('id as qna_id','title','content','type','status','answer_title','answer','created_at','answered_at','file_src') 
                    ->where('id',$qna_id)
                    ->first();


        $return->status = "200";
        $return->data = $rows;

        return response()->json($return, 200)->withHeaders([
            'Content-Type' => 'application/json'
        ]);

        
    }

    public function list_admin(Request $request){
  
        $start_date = $request->start_date;     
        $end_date = $request->end_date;
        $keyword = $request->keyword;
        
        $page_no = $request->page_no;
        $start_no = ($page_no - 1) * 30 ;

        $return = new \stdClass;

        $login_user = Auth::user();
        $user_id = $login_user->getId();
         
        $rows = Qna::select('qnas.id as qna_id','title','type','status','users.name','qnas.created_at')
                    ->join('users', 'qnas.user_id', '=', 'users.id')
                    ->when($keyword, function ($query, $keyword) {
                        return $query->where('title', 'like', "%".$keyword."%");
                    })
                    ->whereBetween('qnas.created_at',[$start_date.' 00:00:00',$end_date.' 23:59:59']) 
                    ->where('qnas.id','>',$start_no) 
                    ->orderby('qnas.id','desc')
                    ->get();


        $return->status = "200";
        $return->cnt = count($rows);
        $return->data = $rows;

        return response()->json($return, 200)->withHeaders([
            'Content-Type' => 'application/json'
        ]);

        
    }

    public function detail_admin(Request $request){
  
        $qna_id = $request->qna_id;     
        
        $return = new \stdClass;
         
        $rows = Qna::select('qnas.id as qna_id','title','type','status','users.name','users.phone','content','qnas.created_at','answer')
                    ->join('users', 'qnas.user_id', '=', 'users.id')
                    ->where('qnas.id',$qna_id) 
                    ->first();


        $return->status = "200";
        $return->data = $rows;

        return response()->json($return, 200)->withHeaders([
            'Content-Type' => 'application/json'
        ]);

        
    }

    public function delete(Request $request)
    {
        $return = new \stdClass;        
    
        $ids = explode(',',$request->qna_id);
        $result = Qna::whereIn('id',$ids)->delete();

        if($result){
            $return->status = "200";
            $return->msg = "success";

        }else{
            $return->status = "500";
            $return->msg = "fail";
        }

        echo(json_encode($return));    

    }


}
