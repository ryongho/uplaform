<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\PartnerInfo;
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

        $result = PartnerInfo::insertGetId([
            'user_id'=> $request->user_id,
            'service_type'=> $request->service_type,
            'partner_type'=> $request->partner_type,
            'confirm_history'=> $request->confirm_history,
            'activity_distance'=> $request->activity_distance,
            'license_img'=> $request->license_img,
            'reg_img'=> $request->reg_img,
            'biz_type'=> $request->biz_type,
            'reg_no'=> $request->biz_reg_no,
            'biz_name'=> $request->biz_name,
            'address'=> $request->address,
            'address2'=> $request->address2 ,
            'ceo_name'=> $request->ceo_name,
            'tel'=> $request->tel,
            'position'=> $request->position,
            'job'=> $request->job,
            'created_at' => Carbon::now(),
        ]);

    
        if($result){
            $return->status = "200";
            $return->msg = "success";
        }
    
    
        return response()->json($return, 200)->withHeaders([
            'Content-Type' => 'application/json'
        ]);;

        //return view('user.profile', ['user' => User::findOrFail($id)]);
    }


    
}
