<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;


class PartnerController extends Controller
{
    public function regist(Request $request)
    {
        //dd($request);

        $return = new \stdClass;

        $return->status = "500";
        $return->msg = "관리자에게 문의";
        $return->data = $request->user_id;

        /* 중복 체크 - start*/
        $id_cnt = Partner::where('user_id',$request->user_id)->count();
        $email_cnt = Partner::where('email',$request->email)->count();
        $phone_cnt = Partner::where('phone',$request->phone)->count();

        if($id_cnt){
            $return->status = "601";
            $return->msg = "사용중인 아이디";
            $return->data = $request->user_id;
        }else if($email_cnt){
            $return->status = "602";
            $return->msg = "사용중인 이메일";
            $return->data = $request->email;
        }else if ($phone_cnt){
            $return->status = "603";
            $return->msg = "사용중인 폰 번호";
            $return->data = $request->phone;
        /* 중복 체크 - end*/
        }else{
            $result = User::insert([
                'name'=> $request->name ,
                'email' => $request->email, 
                'password' => $request->password, 
                'user_id' => $request->user_id,
                'phone' => $request->phone, 
                'user_type' => 1,
                'created_at' => Carbon::now(),
                'password' => Hash::make($request->password)
                
            ]);

            if($result){
                $return->status = "200";
                $return->msg = "success";
                $return->data = $request->user_id;
            }
        }
        

        return response()->json($return, 200)->withHeaders([
            'Content-Type' => 'application/json'
        ]);;     

    }


    public function login(Request $request){
        $partner = User::where('user_id' , $request->user_id)->where('user_type', 1 )->first();

        $return = new \stdClass;

        if (Hash::check($request->password, $partner->password)) {
            //dd($partner);
            //echo("로그인 확인");
            Auth::loginUsingId($partner->id);
            $login_user = Auth::user();

            $token = $login_user->createToken('partner');

            $return->status = "200";
            $return->msg = "success";
            $return->token = $token;

        }else{
            $return->status = "500";
            $return->msg = "아이디 또는 패스워드가 일치하지 않습니다.";
            $return->data = $request->user_id;
        }

        return response()->json($return, 200)->withHeaders([
            'Content-Type' => 'application/json'
        ]);;
    }

    public function logout(Request $request){
        $user = Auth::user(); 
        Auth::logout();
        
    }

    public function login_check(Request $request){
        
        $login_user = Auth::user();
        dd($login_user);
        

        $return = new \stdClass;

        $return->status = "500";
        $return->msg = "관리자에게 문의";
        $return->data = $request->user_id;
        $return->user_type = "partner";

        return response()->json($return, 200)->withHeaders([
            'Content-Type' => 'application/json'
        ]);;
    
        //$result = auth('api')->check();
        //dd($result);
        //return $request->user();
        
    }

    public function find_partner_id(Request $request){
        $partner = Partner::where('phone' , $request->phone)->first();
        
        $msg = "";
        if (isset($partner->id)) {
            $msg = "파트너 아이디는 ".$partner->partner_id." 입니다.";
        }else{
            $msg = "등록되지 않은 연락처 입니다.";  
        }
        $return = new \stdClass;
        
        $return->status = "200";
        $return->msg = $msg;
        $return->partner_id = $partner->partner_id ;
        
        return response()->json($return, 200)->withHeaders([
            'Content-Type' => 'application/json'
        ]);;
    }

    public function list(Request $request){
        $start_no = $request->start_no;
        $row = $request->row;
        
        $rows = User::where('id' ,">=", $start_no)->where('user_type','1')->orderBy('id', 'desc')->limit($row)->get();

        $list = new \stdClass;

        $list->status = "200";
        $list->msg = "success";
        $list->cnt = count($rows);
        $list->data = $rows;
        
        return response()->json($list, 200)->withHeaders([
            'Content-Type' => 'application/json'
        ]);;
        
    }
}
