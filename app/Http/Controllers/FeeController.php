<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\Fee;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class FeeController extends Controller
{
    

    public function update(Request $request)
    {
        //dd($request);
        $return = new \stdClass;


        $result = Fee::where('type',$request->type)->update([
            'fee'=> $request->fee ,
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


    public function get_fee(Request $request){

        $return = new \stdClass;

        $rows = Fee::select('id as fee_id','type','fee','created_at','updated_at') 
                    ->get();


        $return->status = "200";
        $return->data = $rows;

        return response()->json($return, 200)->withHeaders([
            'Content-Type' => 'application/json'
        ]);

        
    }

    

    



}
