<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Device;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DeviceController extends Controller
{
    public function regist(Request $request)
    {
        //dd($request);

        $return = new \stdClass;

        $login_user = Auth::user();
        $user_id = $login_user->getId();

        $cnt = Device::where('user_id',$user_id)->count();
        if(!$cnt){
            $result = Device::insert([
                'user_id'=> $user_id ,
                'push_id'=> $request->push_id ,
                'os'=> $request->os ,
                'app_version'=> $request->app_version ,
                'model'=> $request->model ,
                'created_at'=> Carbon::now(),
            ]);
            $return->status = "200";
            
        }else{
            $result = Device::where('user_id', $user_id)->update([
                'user_id'=> $user_id ,
                'push_id'=> $request->push_id ,
                'os'=> $request->os ,
                'app_version'=> $request->app_version ,
                'model'=> $request->model ,
                'created_at'=> Carbon::now(),
            ]);
            $return->status = "200";
                   
        }

        return response()->json($return, 200)->withHeaders([
            'Content-Type' => 'application/json'
        ]);;



    }



}
