<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\AreaInfo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;


class AreaController extends Controller
{
    public function regist(Request $request)
    {
        //dd($request);
        $return = new \stdClass;

        $return->status = "500";
        $return->msg = "관리자에게 문의";

    
        $result = AreaInfo::insertGetId([
            'user_id'=> $request->user_id,
            'position'=> $request->position ,
            'interest_service'=> $request->interest_service ,
            'house_type'=> $request->house_type ,
            'peoples'=> $request->peoples ,
            'house_size'=> $request->house_size ,
            'area_size'=> $request->area_size ,
            'address'=> $request->address ,
            'address2'=> $request->address2 ,
            'tel'=> $request->tel ,
            'shop_type'=> $request->shop_type ,
            'shop_size'=> $request->shop_size ,
            'kitchen_size'=> $request->kitchen_size ,
            'refrigerator'=> $request->refrigerator ,
            'refrigerator_size'=> $request->refrigerator_size ,
            'shop_name'=> $request->shop_name ,
            'ceo_name'=> $request->ceo_name ,
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
