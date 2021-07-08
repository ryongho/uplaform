<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Hotel;
use Illuminate\Support\Carbon;

class HotelController extends Controller
{
    public function regist(Request $request)
    {
        //dd($request);

        Hotel::insert([
            'partner_id'=> $request->partner_id ,
            'name'=> $request->name ,
            'content'=> $request->content ,
            'owner'=> $request->owner ,
            'reg_no'=> $request->reg_no ,
            'open_date'=> $request->open_date ,
            'address'=> $request->address ,
            'tel'=> $request->tel ,
            'fax'=> $request->fax ,
            'email'=> $request->email ,
            'traffic'=> $request->traffic ,
            'level'=> $request->level ,
            'created_at' => Carbon::now()
        ]);
    }



}
