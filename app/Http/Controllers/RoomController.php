<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Hotel;
use App\Models\Room;
use App\Models\RoomImage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class RoomController extends Controller
{
    public function regist(Request $request)
    {
        //dd($request);

        $return = new \stdClass;

        $return->status = "500";
        $return->msg = "관리자에게 문의";
        $return->data = $request->name;

        $hotel_id = $request->hotel_id;

        $login_user = Auth::user();
        $user_id = $login_user->getId();
        $user_type = $login_user->getType();

        $cnt = Hotel::where('partner_id',$user_id)->where('id',$hotel_id)->count();
        
        if($cnt == 0 || $user_id == ""){// 아이디 존재여부
            $return->status = "601";
            $return->msg = "해당 호텔에 객실을 등록 할 수 없는 계정입니다.";
            $return->data = $request->name ;
        }elseif( $user_type == 0 ){//일반회원
            $return->status = "602";
            $return->msg = "일반 회원입니다.";
            $return->data = $request->name ;
        }else{
            $result = Room::insertGetId([
                'hotel_id'=> $request->hotel_id ,
                'name'=> $request->name ,
                'size'=> $request->size ,
                'bed'=> $request->bed ,
                'amount'=> $request->amount ,
                'peoples'=> $request->peoples ,
                'options'=> $request->options ,
                'price'=> $request->price ,
                'checkin'=> $request->checkin ,
                'checkout'=> $request->checkout ,
                'created_at'=> Carbon::now(),
            ]);

            if($result){ //DB 입력 성공

                $no = 1; 

                $images = explode(",",$request->images);
                foreach( $images as $image){
                
                    $result_img = RoomImage::insertGetId([
                        'room_id'=> $result ,
                        'file_name'=> $image ,
                        'order_no'=> $no ,
                        'created_at' => Carbon::now()
                    ]);
    
                    $no++;
                }

    
                $return->status = "200";
                $return->msg = "success";
                $return->insert_id = $result ;

            }
        }

        echo(json_encode($return));    

    }

    public function list(Request $request){
        $s_no = $request->start_no;
        $row = $request->row;

        $rows = Room::where('id','>=',$s_no)->orderBy('id', 'desc')->limit($row)->get();

        $return = new \stdClass;

        $return->status = "200";
        $return->cnt = count($rows);
        $return->data = $rows ;

        echo(json_encode($return));

    }

    public function list_for_select(Request $request){

        $login_user = Auth::user();

        $hotel_info = Hotel::where('partner_id',$login_user->id)->get();

        $rows = Room::where('hotel_id',$hotel_info[0]->id)->orderBy('id', 'desc')->get();

        $return = new \stdClass;

        $return->status = "200";
        $return->cnt = count($rows);
        $return->data = $rows ;

        echo(json_encode($return));

    }

    public function detail(Request $request){
        $id = $request->id;

        $rows = Room::where('id','=',$id)->get();
        $images = RoomImage::where('room_id','=',$id)->orderBy('order_no')->get();

        $return = new \stdClass;

        $return->status = "200";
        $return->data = $rows ;
        $return->images = $images ;

        echo(json_encode($return));

    }

    



}
