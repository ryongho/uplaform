<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function regist(Request $request)
    {
        //dd($request);

        User::insert([
            'name'=> $request->name ,
            'email' => $request->email, 
            'password' => $request->password, 
            'user_id' => $request->user_id,
            'phone' => $request->phone, 
            'user_type' => $request->user_type,
            'created_at' => Carbon::now(),
            'password' => Hash::make($request->password)
            
        ]);

        //return view('user.profile', ['user' => User::findOrFail($id)]);
    }

    public function login(Request $request){
        $user = User::where('user_id' , $request->user_id)->first();
        
        if (Hash::check($request->password, $user->password)) {
            //echo("로그인 확인");
            Auth::loginUsingId($user->id);
            $login_user = Auth::user();

            $token = $login_user->createToken('user');
            
            //dd($token->plainTextToken);    
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
