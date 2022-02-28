<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\Notice;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class NoticeController extends Controller
{
    public function regist(Request $request)
    {
        
        $return = new \stdClass;   

        $login_user = Auth::user();
        
        $result = Notice::insertGetId([
            'title'=> $request->title ,
            'content'=> $request->content ,
            'start_date'=> $request->start_date ,
            'end_date'=> $request->end_date ,
            'usable'=> $request->usable ,
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
        

        return response()->json($return, 200)->withHeaders([
            'Content-Type' => 'application/json'
        ]);;
    }

    public function list(Request $request){

        $s_no = $request->start_no;
        $row = $request->row;

        $rows = Notice::select('id as notice_id','created_at', 'title')->where('id','>',$s_no)->limit($row)->get();

        $return = new \stdClass;

        $return->status = "200";
        $return->cnt = count($rows);
        $return->data = $rows ;

        return response()->json($return, 200)->withHeaders([
            'Content-Type' => 'application/json'
        ]);;

    }

    public function list_admin(Request $request){

        $page_no = $request->page_no;
        $row = $request->row;
        $offset = (($page_no-1) * $row);
        $usable = $request->usable;
        $start_date = $request->start_date;
        $end_date = $request->end_date;

        $rows = Notice::select('id as notice_id','title','start_date', 'end_date', 'usable', 
                        DB::raw('(select name from users where id = notices.writer ) as writer'), 
                        'created_at',)
                ->when($usable, function ($query, $usable) {
                    if($usable != "전체"){//확정대기
                        return $query->where('usable', $usable);
                    }
                })     
                ->where('created_at','>=', $start_date)
                ->where('created_at','<=', $end_date.' 23:59:59')        
                ->orderby('id','desc')
                ->offset($offset)
                ->limit($row)
                ->get();

        $cnt = Notice::when($usable, function ($query, $usable) {
                        if($usable != "전체"){//확정대기
                            return $query->where('usable', $usable);
                        }
                    })     
                    ->where('created_at','>=', $start_date)
                    ->where('created_at','<=', $end_date.' 23:59:59')        
                    ->count();

        $return = new \stdClass;

        $return->status = "200";
        $return->cnt = $cnt;
        $return->data = $rows ;

        return response()->json($return, 200)->withHeaders([
            'Content-Type' => 'application/json'
        ]);;

    }

    public function detail_admin(Request $request){
        $notice_id = $request->notice_id;

        $rows = Notice::select('id as notice_id','title','content','start_date', 'end_date', 'usable', 
                                DB::raw('(select name from users where id = notices.writer ) as writer'), 
                                'created_at')
                        ->where('id',$notice_id)
                        ->first();

        $return = new \stdClass;

        $return->status = "200";
        $return->data = $rows ;

        return response()->json($return, 200)->withHeaders([
            'Content-Type' => 'application/json'
        ]);;

    }

    public function detail(Request $request){
        $notice_id = $request->notice_id;

        $rows = Notice::select('id as notice_id','created_at', 'title','content')->where('id','=',$notice_id)->get();

        $pre = Notice::select('id as notice_id','title')->where('id','<',$notice_id)->orderby('id','desc')->limit(1)->first();
        $next = Notice::select('id as notice_id','title')->where('id','>',$notice_id)->orderby('id','asc')->limit(1)->first();

        $return = new \stdClass;

        $return->status = "200";
        $return->data = $rows ;
        $return->pre_data = $pre ;
        $return->next_data = $next ;

        return response()->json($return, 200)->withHeaders([
            'Content-Type' => 'application/json'
        ]);;

    }

    public function update(Request $request)
    {
        //dd($request);
        $return = new \stdClass;

        $return->status = "500";
        $return->msg = "관리자에게 문의";

        $login_user = Auth::user();

        $result = Notice::where('id',$request->notice_id)->update([
            'title'=> $request->title ,
            'content'=> $request->content ,
            'start_date'=> $request->start_date ,
            'end_date'=> $request->end_date ,
            'usable'=> $request->usable ,
            'writer'=> $login_user->getId(),
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
        ]);;

    }

    



}
