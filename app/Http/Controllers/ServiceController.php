<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\Service;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class ServiceController extends Controller
{
    

    public function list(Request $request){

        $service_type = $request_service_type;
        $service_sub_type = $service_sub_type;
        $service_part = $request_service_part;


        $rows = Service::select(   
                            'id',  
                            'service_type', 
                            'service_sub_type', 
                            'request_service_part', 
                            'goods_name', 
                            'price', 
                                    
                        )         
                        ->where('service_type' , $request->service_type)
                        ->when($service_sub_type, function ($query, $service_sub_type) {
                            return $query->where('service_sub_type', $service_sub_type);
                        })
                        ->when($service_part, function ($query, $service_part) {
                            return $query->where('service_part', $service_part);
                        })
                        ->orderBy('id','asc')
                        ->get();

        $return = new \stdClass;

        $return->status = "200";
        $return->cnt = count($rows);
        $return->data = $rows ;

        return response()->json($return, 200)->withHeaders([
            'Content-Type' => 'application/json'
        ]);;

    }

    

    



}
