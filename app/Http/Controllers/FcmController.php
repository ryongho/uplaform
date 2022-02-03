<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class FcmController extends Controller
{

    public function update(Request $request)
    {
        //dd($request);
        $return = new \stdClass;

        $return->status = "500";
        $return->msg = "관리자에게 문의";

        $user_id = $request->user_id;
        $fcm_token = $request->fcm_token;
        
        /* 중복 체크 - start*/

    
        $result = User::where('id',$user_id)->update([
            'fcm_token'=> $request->fcm_token 
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
