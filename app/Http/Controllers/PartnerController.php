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

        Partner::insert([
            'name'=> $request->name ,
            'email' => $request->email, 
            'password' => $request->password, 
            'user_id' => $request->user_id,
            'phone' => $request->phone, 
            'created_at' => Carbon::now(),
            'password' => Hash::make($request->password)
        ]);

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
    

    public function find_user_id(Request $request){
        $user = User::where('phone' , $request->phone)->first();
        
        if (isset($user->id)) {
            echo("사용자 아이디는 ".$user->user_id." 입니다.");       
        }else{
            echo("등록되지 않은 연락처 입니다.");       
        }
    }
}
