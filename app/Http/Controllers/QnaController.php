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
        $login_user = Auth::user();
        $user_id = $login_user->getId();

        $result = Qna::where('id', $qna_id)->update([
            'answer_title' => $request->answer_title,
            'answer' => $request->answer,
            'admin_id' => $user_id,
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

    public function list_admin(Request $request){

        $page_no = $request->page_no;
        $row = $request->row;
        $user_type = $request->user_type;
        $status = $request->status;
        $offset = (($page_no-1) * $row);
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $search_keyword = $request->search_keyword;

        $return = new \stdClass;
        
        $rows = Qna::join('users', 'users.id', '=', 'qnas.user_id')
                ->select('qnas.id as qna_id','users.user_type',
                        'users.email','title','content','qnas.type','qnas.status',
                        'qnas.created_at','qnas.answered_at','qnas.updated_at', 
                        DB::raw('(select name from users where id = qnas.admin_id ) as admin_name'),
                )
                ->when($status, function ($query, $status) {
                    if($status != "전체"){//확정대기
                        return $query->where('qnas.status', $status);
                    }
                })
                ->when($user_type != null, function ($query, $user_type) {
                    if($user_type == "전체"){//확정대기
                        return $query->whereIn('users.user_type', [0,1]);
                    }else{
                        if($user_type == "기업"){
                            return $query->where('users.user_type', 1);
                        }else if($user_type == "일반"){
                            return $query->where('users.user_type', 0);
                        }
                        
                    }
                })
                ->when($search_keyword, function ($query, $search_keyword) {
                    return $query->where('qnas.title','like', "%".$search_keyword."%");
                })
                ->where('qnas.created_at','>=', $start_date)
                ->where('qnas.created_at','<=', $end_date.' 23:59:59')        
                ->orderby('qnas.id','desc')
                ->offset($offset)
                ->limit($row)
                ->get();

        $cnt = Qna::when($status, function ($query, $status) {
                    if($status != "전체"){//확정대기
                        return $query->where('status', $status);
                    }
            })
            ->when($user_type != null, function ($query, $user_type) {
                if($user_type == "전체"){//확정대기
                    return $query->whereIn('users.user_type', ['0','1']);
                }else{
                    if($user_type == "기업"){
                        return $query->where('users.user_type', '1');
                    }else if($user_type == "일반"){
                        return $query->where('users.user_type', '0');
                    }
                    
                }
            })
            ->when($search_keyword, function ($query, $search_keyword) {
                return $query->where('title','like', "%".$search_keyword."%");
            })
            ->where('created_at','>=', $start_date)
            ->where('created_at','<=', $end_date.' 23:59:59')        
            ->count();

        $return->status = "200";
        $return->cnt = $cnt;
        $return->data = $rows;

        return response()->json($return, 200)->withHeaders([
            'Content-Type' => 'application/json'
        ]);

        
    }

    public function detail_admin(Request $request){
  
        $qna_id = $request->qna_id;     
        
        $return = new \stdClass;
         
        $rows = Qna::join('users', 'users.id', '=', 'qnas.user_id')
                    ->select('qnas.id as qna_id','users.user_type',
                        'users.email','title','content','qnas.type','qnas.status',
                        'qnas.created_at','qnas.answered_at','qnas.updated_at',
                        'answer_title',
                        'answer', 
                        DB::raw('(select name from users where id = qnas.admin_id ) as admin_name'),)
                    ->where('qnas.id',$qna_id) 
                    ->first();


        $return->status = "200";
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
