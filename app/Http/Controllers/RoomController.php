<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Room;
use Illuminate\Support\Carbon;

class RoomController extends Controller
{
    public function regist(Request $request)
    {
        //dd($request);

        Room::insert([
            'hotel_id'=> $request->hotel_id ,
            'name'=> $request->name ,
            'size'=> $request->size ,
            'bed'=> $request->bed ,
            'amount'=> $request->amount ,
            'peoples'=> $request->peoples ,
            'options'=> $request->options ,
            'price'=> $request->price ,
            'created_at'=> Carbon::now(),
        ]);
    }



}
