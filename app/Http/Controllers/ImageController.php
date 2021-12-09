<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class ImageController extends Controller
{
    public function upload(Request $request)
    {
        //dd($request);

        $return = new \stdClass;

        $return->status = "500";
        $return->msg = "관리자에게 문의";
        $type = $request->type;
        

        $no = 0; 

        $images = array();

        foreach($request->file() as $file){// 객실 이미지 업로드

            $images[$no] = Storage::disk('s3')->put($type."_images", $file,'public');     
            $no++;
        } 

        $return->status = "200";
        $return->msg = "success";
        $return->images = stripslashes($images);

        return response()->json($return, 200)->withHeaders([
            'Content-Type' => 'application/json'
        ]);;    

    }


}
