<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\Faq;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class FaqController extends Controller
{
    public function regist(Request $request)
    {
        
        $return = new \stdClass;   

        $login_user = Auth::user();
        

        $result = Faq::insertGetId([
            'type'=> $request->type ,
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

        $type = $request->type;

        $rows = Faq::select('id as faq_id','type','title','created_at')
                ->when($type , function ($query, $type) {
                    if($type != "전체"){
                        return $query->where('type', $type);
                    }
                    
                })
                ->get();

        $return = new \stdClass;

        $return->status = "200";
        $return->cnt = count($rows);
        $return->data = $rows ;

        return response()->json($return, 200)->withHeaders([
            'Content-Type' => 'application/json'
        ]);;

    }

    public function detail(Request $request){
        $faq_id = $request->faq_id;

        $rows = Faq::select('id as faq_id','type','title','content','created_at')->where('id','=',$faq_id)->get();

        $return = new \stdClass;

        $pre = Faq::select('id as faq_id','title')->where('id','<',$faq_id)->orderby('id','desc')->limit(1)->first();
        $next = Faq::select('id as faq_id','title')->where('id','>',$faq_id)->orderby('id','asc')->limit(1)->first();

        $return->status = "200";
        $return->data = $rows ;
        $return->pre_data = $pre ;
        $return->next_data = $next ;

        return response()->json($return, 200)->withHeaders([
            'Content-Type' => 'application/json'
        ]);;

    }

    public function list_admin(Request $request){

        $page_no = $request->page_no;
        $row = $request->row;
        $type = $request->type;
        $offset = (($page_no-1) * $row);
        $usable = $request->usable;
        $start_date = $request->start_date;
        $end_date = $request->end_date;

        $rows = Faq::select('id as faq_id','type', 'title','start_date', 'end_date', 'usable', 
                        DB::raw('(select name from users where id = faqs.writer ) as writer'), 
                        'created_at',)
                ->when($usable, function ($query, $usable) {
                    if($usable != "전체"){
                        return $query->where('usable', $usable);
                    }
                })
                ->when($type, function ($query, $type) {
                    if($type != "전체"){
                        return $query->where('type', $type);
                    }
                })     
                ->where('created_at','>=', $start_date)
                ->where('created_at','<=', $end_date.' 23:59:59')        
                ->orderby('id','desc')
                ->offset($offset)
                ->limit($row)
                ->get();

        $cnt = Faq::when($usable, function ($query, $usable) {
                        if($usable != "전체"){
                            return $query->where('usable', $usable);
                        }
                    })
                    ->when($type, function ($query, $type) {
                        if($type != "전체"){
                            return $query->where('type', $type);
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
        $faq_id = $request->faq_id;

        $rows = Faq::select('id as faq_id','title','content','start_date', 'end_date', 'usable','type', 
                                DB::raw('(select name from users where id = faqs.writer ) as writer'), 
                                'created_at')
                        ->where('id',$faq_id)
                        ->first();

        $return = new \stdClass;

        $return->status = "200";
        $return->data = $rows ;

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

        $result = Faq::where('id',$request->faq_id)->update([
            'title'=> $request->title ,
            'type'=> $request->type ,
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

    public function delete(Request $request)
    {
        $return = new \stdClass;        
    
        $ids = explode(',',$request->faq_id);
        $result = Faq::whereIn('id',$ids)->delete();

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
