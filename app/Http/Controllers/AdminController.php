<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use PHPMailer\PHPMailer\Exception;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function regist(Request $request)
    {
        //dd($request);
        $return = new \stdClass;

        $return->status = "500";
        $return->msg = "관리자에게 문의";

        $login_user = Auth::user();
        $user_id = $request->user_id;

        /* 중복 체크 - start*/
        $email_cnt = User::where('email',$user_id)->count();

        if($login_user->user_type < 4){
            $return->status = "601";
            $return->msg = "권한이 없습니다.";
            $return->data = "현재 유저 타입 : ".$request->user_type;
        }else if($email_cnt){
            $return->status = "602";
            $return->msg = "사용중인 아이디";
            $return->data = $request->user_id;
        }else{
            $result = User::insert([
                'name'=> $request->name ,
                'email' => $user_id,
                'sns_key' => $request->email,
                'phone' => $request->phone,
                'user_type' => 3, // 관리자 
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'part' => $request->part,
                'permission' => $request->permission,
                'created_at' => Carbon::now(),
                'password' => Hash::make($request->password)
            ]);

            if($result){
                $return->status = "200";
                $return->msg = "관리자 등록 성공";
            }
        }    
        

        return response()->json($return, 200)->withHeaders([
            'Content-Type' => 'application/json'
        ]);

        //return view('user.profile', ['user' => User::findOrFail($id)]);
    }

    

    public function login(Request $request){

        $user = User::where('email' , $request->email)->whereIn('user_type', [3,4])->first();

        $return = new \stdClass;

        if(!$user){
            $return->status = "501";
            $return->msg = "존재하지 않는 아이디 입니다.";
            $return->email = $request->email;
        }else if (Hash::check($request->password, $user->password)) {
            //echo("로그인 확인");
            Auth::loginUsingId($user->id);
            $login_user = Auth::user();

            $token = $login_user->createToken('user');

            $return->status = "200";
            $return->msg = "성공";
            $return->token = $token->plainTextToken;
            $return->user_type = $login_user->user_type;

            User::where('email',$request->user_id)->update([
                'last_login' =>Carbon::now(),
            ]);
        }else{
            $return->status = "500";
            $return->msg = "아이디 또는 패스워드가 일치하지 않습니다.";
            $return->email = $request->email;
        }

        return response()->json($return, 200)->withHeaders([
            'Content-Type' => 'application/json'
        ]);
    }

    public function logout(Request $request){
        $user_info = Auth::user();
        $user = User::where('id', $user_info->id)->first();
        $user->tokens()->delete();

        $return = new \stdClass;
        $return->status = "200";
        $return->msg = "success";

        return response()->json($return, 200)->withHeaders([
            'Content-Type' => 'application/json'
        ]);
    }
    
    public function list(Request $request){

        $login_user = Auth::user();
        $user_id = $request->user_id;
        $page_no = $request->page_no;
        $row = $request->row;
        $offset = (($page_no-1) * $row);

        $search = new \stdClass;
        $search->type = $request->search_type;
        $search->keyword = $request->search_keyword;
        
        $list = new \stdClass;

        if($login_user->user_type < 4){
            $list->status = "601";
            $list->msg = "권한이 없습니다.";
            $list->data = "현재 유저 타입 : ".$request->user_type;
        }else {
            
            $rows = User::select('id','user_type','email as user_id','phone','name','part','sns_key as email','permission','start_date','end_date','created_at')
            ->whereIn('user_type',['3','4'])
            ->when($search, function ($query, $search) {
                if($search->type == "name"){
                    return $query->where('name', 'like', '%'.$search->keyword.'%');
                }else if($search->type == "phone"){
                    return $query->where('phone', $search->keyword);
                }else if($search->type == "email"){
                    return $query->where('sns_key', $search->keyword);
                }
            })
            ->offset($offset)
            ->orderBy('id', 'desc')
            ->limit($row)
            ->get();

            $cnt = User::whereIn('user_type',['3','4'])
                    ->when($search, function ($query, $search) {
                        if($search->type == "name"){
                            return $query->where('name', 'like', '%'.$search->keyword.'%');
                        }else if($search->type == "phone"){
                            return $query->where('phone', $search->keyword);
                        }else if($search->type == "email"){
                            return $query->where('sns_key', $search->keyword);
                        }
                    })
                    ->count();

            $list->status = "200";
            $list->msg = "success";
            $list->cnt = $cnt;
            $list->data = $rows;
        }
        
        
        return response()->json($list, 200)->withHeaders([
            'Content-Type' => 'application/json'
        ]);
        
    }

    public function detail(Request $request){

        $id = $request->id;

        $login_user = Auth::user();
        $user_id = $request->user_id;

        $list = new \stdClass;

        if($login_user->user_type < 4){
            $return->status = "601";
            $return->msg = "권한이 없습니다.";
            $return->data = "현재 유저 타입 : ".$request->user_type;
        }else {
            $rows = User::select('id','email as user_id','name','part','sns_key as email','permission','start_date','end_date','created_at','last_login')
                    ->where('id',$id)->first();

            if($rows){
                $list->status = "200";
                $list->msg = "success";
                $list->data = $rows;
            }else{
                $list->status = "500";
                $list->msg = "해당 정보가 없습니다.";
            }

        }
        
        return response()->json($list, 200)->withHeaders([
            'Content-Type' => 'application/json'
        ]);
        
    }

    

    public function update(Request $request){
        
        $return = new \stdClass;
        
        $id = $request->id;

        $login_user = Auth::user();
        $user_id = $request->user_id;

        if($login_user->user_type < 4){
            $return->status = "601";
            $return->msg = "권한이 없습니다.";
            $return->data = "현재 유저 타입 : ".$request->user_type;
        }else {
            $result = User::where('id',$id)->update([
                'name'=> $request->name ,
                'sns_key' => $request->email,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'phone' => $request->phone,
                'part' => $request->part,
                'permission' => $request->permission, 
                
            ]);
     
            if($result){
                $return->status = "200";
                $return->msg = "변경 완료";
            }else{
                $return->status = "500";
                $return->msg = "변경 실패";
            }
        }
        
        return response()->json($return, 200)->withHeaders([
            'Content-Type' => 'application/json'
        ]);

    }

    public function update_password(Request $request){
        //dd($request);
        $return = new \stdClass;
        $login_user = Auth::user();
        $id = $request->id;

        if($login_user->user_type < 4){
            $return->status = "601";
            $return->msg = "권한이 없습니다.";
            $return->data = "현재 유저 타입 : ".$request->user_type;
        }else {

            $value = Hash::make($request->password);
        
            $result = User::where('id', $id)->update(["password" => $value]);

            if($result){
                $return->status = "200";
                $return->msg = "변경 성공";
            }else{
                $return->status = "500";
                $return->msg = "변경 실패";
            }


        }
        
        return response()->json($return, 200)->withHeaders([
            'Content-Type' => 'application/json'
        ]);

    }

    public function delete(Request $request)
    {
        $return = new \stdClass;        
    
        $ids = explode(',',$request->admin_id);
        $result = User::whereIn('id',$ids)->delete();

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
