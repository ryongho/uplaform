<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Partner;
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
            $result = Partner::insert([
                'name'=> $request->name ,
                'email' => $request->email, 
                'password' => $request->password, 
                'user_id' => $request->user_id,
                'phone' => $request->phone, 
                'created_at' => Carbon::now(),
                'password' => Hash::make($request->password)
            ]);

            if($result){
                $return->status = "200";
                $return->msg = "success";
                $return->data = $request->user_id;
            }
        }
        

        echo(json_encode($return));        

    }


    public function login(Request $request){
        $partner = Partner::where('user_id' , $request->user_id)->first();
        
        if (Hash::check($request->password, $partner->password)) {
            //dd($partner);
            //echo("로그인 확인");
            Auth::guard('partner')->loginUsingId($partner->id);
            $login_user = Auth::guard('partner')->user();

            $token = $login_user->createToken('partner');
            
            dd($token->plainTextToken);    
        }
    }

    public function logout(Request $request){
        $user = Auth::user(); 
        Auth::logout();
        
    }

    public function login_check(Request $request){
        $user = Auth::user(); 
        dd($user);
        
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
        
        echo(json_encode($list));
    }

    public function list(Request $request){
        $start_no = $request->start_no;
        $cnt = $request->cnt;
        
        $partners = Partner::where('id' ,">=", $start_no)->orderBy('id')->limit($cnt)->get();

        $list = new \stdClass;

        $list->status = "200";
        $list->msg = "success";
        $list->data = $partners;
        
        echo(json_encode($list));
        
    }
}
