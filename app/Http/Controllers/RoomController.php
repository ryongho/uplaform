<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Hotel;
use App\Models\Room;
use App\Models\RoomImage;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

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

        $rows = Room::join('hotels', 'rooms.hotel_id', '=', 'hotels.id')
                    ->select('*',
                            'rooms.options as room_options',
                            'hotels.options as hotel_options',
                            DB::raw('(select file_name from room_images where room_images.room_id = rooms.id order by order_no asc limit 1 ) as thumb_nail')
                            )
                    ->where('rooms.id','>=',$s_no)->orderBy('rooms.id', 'desc')->limit($row)->get();

        $return = new \stdClass;

        $return->status = "200";
        $return->cnt = count($rows);
        $return->data = $rows ;

        echo(json_encode($return));

    }

    public function list_for_select(Request $request){

        $login_user = Auth::user();

        $hotel_info = Hotel::where('partner_id',$login_user->id)->get();

        $rows = Room::select('*',DB::raw('(select file_name from room_images where room_images.room_id = rooms.id order by order_no asc limit 1 ) as thumb_nail'))
                ->where('hotel_id',$hotel_info[0]->id)->orderBy('id', 'desc')->get();

        $return = new \stdClass;

        $return->status = "200";
        $return->cnt = count($rows);
        $return->data = $rows ;

        echo(json_encode($return));

    }


    public function list_by_hotel(Request $request){

        $hotel_id = $request->hotel_id;

        $hotel_info = Hotel::where('id',$hotel_id)->get();

        $rows = Room::select('*',DB::raw('(select file_name from room_images where room_images.room_id = rooms.id order by order_no asc limit 1 ) as thumb_nail'))
                ->where('hotel_id',$hotel_info[0]->id)->orderBy('id', 'desc')->get();

        $return = new \stdClass;

        $return->status = "200";
        $return->cnt = count($rows);
        $return->data = $rows ;

        echo(json_encode($return));

    }

    public function list_by_partner(Request $request){

        $login_user = Auth::user();

        $hotel_info = Hotel::select('id')->where('hotels.partner_id',$login_user->id)->get();

        $rows = array();
        $rn = 0;

        foreach($hotel_info as $hotel){
            $rows[$rn] = Room::select('*',DB::raw('(select file_name from room_images where room_images.room_id = rooms.id order by order_no asc limit 1 ) as thumb_nail'))
                ->where('hotel_id',$hotel->id)
                ->orderBy('rooms.id', 'desc')->get();
            
            $rn++;

        }        

        $return = new \stdClass;

        $return->status = "200";
        $return->cnt = count($rows);
        $return->data = $rows ;

        echo(json_encode($return));

    }

    public function detail(Request $request){
        $id = $request->id;

        $rows = Room::join('hotels', 'rooms.hotel_id', '=', 'hotels.id')->select('*',
                            'rooms.options as room_options',
                            'hotels.options as hotel_options',
                            'rooms.name as name',
                            DB::raw('(select file_name from room_images where room_images.room_id = rooms.id order by order_no asc limit 1 ) as thumb_nail')
                            )
                    ->where('rooms.id','=',$id)->get();
        $images = RoomImage::where('room_id','=',$id)->orderBy('order_no')->get();

        $return = new \stdClass;

        $return->status = "200";
        $return->data = $rows ;
        $return->images = $images ;

        echo(json_encode($return));

    }


    public function update(Request $request)
    {
        //dd($request);
        $return = new \stdClass;

        $return->status = "500";
        $return->msg = "관리자에게 문의";

        $login_user = Auth::user();
        $user_id = $login_user->getId();
        $user_type = $login_user->getType();

        /* 중복 체크 - start*/
        
        
        $id_cnt = User::where('id',$user_id)->count();

        if($id_cnt == 0 || $user_id == ""){// 아이디 존재여부
            $return->status = "601";
            $return->msg = "fail";
            $return->reason = "유효하지 않은 파트너 아이디 입니다." ;
            $return->data = $request->name ;
        }elseif( $user_type == 0 ){//일반회원
            $return->status = "602";
            $return->msg = "fail";
            $return->reason = "유효하지 않은 파트너 아이디 입니다." ;

            $return->data = $request->name ;
        }else{

            $grant = Hotel::where('id',$request->hotel_id)->where('partner_id',$user_id)->count();
        
            if($grant){

                $result = Room::where('id',$request->id)->where('hotel_id',$request->hotel_id)->update([
                    'name'=> $request->name ,
                    'size'=> $request->size ,
                    'bed'=> $request->bed ,
                    'amount'=> $request->amount ,
                    'peoples'=> $request->peoples ,
                    'options'=> $request->options ,
                    'price'=> $request->price ,
                    'checkin'=> $request->checkin ,
                    'checkout'=> $request->checkout 
                ]);

                if($result){
                    $return->status = "200";
                    $return->msg = "success";
                    $return->updated_id = $result ;
    
                }else{
                    $return->status = "500";
                    $return->msg = "fail";
                }

            }else{
                $return->status = "500";
                $return->msg = "fail";
                $return->reason = "권한이 없습니다." ;
            }            
            
        }
        

        echo(json_encode($return));    

    }

    public function delete(Request $request)
    {
        //dd($request);
        $return = new \stdClass;

        $return->status = "500";
        $return->msg = "관리자에게 문의";

        $login_user = Auth::user();
        $user_id = $login_user->getId();
        $user_type = $login_user->getType();

        /* 중복 체크 - start*/
        
        
        $id_cnt = User::where('id',$user_id)->count();

        if($id_cnt == 0 || $user_id == ""){// 아이디 존재여부
            $return->status = "601";
            $return->msg = "fail";
            $return->reason = "유효하지 않은 파트너 아이디 입니다." ;
            $return->data = $request->name ;
        }elseif( $user_type == 0 ){//일반회원
            $return->status = "602";
            $return->msg = "fail";
            $return->reason = "유효하지 않은 파트너 아이디 입니다." ;

            $return->data = $request->name ;
        }else{
            $room_info = Room::where('id',$request->room_id)->first();
            
            $grant = Hotel::where('id',$room_info->hotel_id)->where('partner_id',$user_id)->count();
            
            if($grant){

                $result = Room::where('id',$request->room_id)->delete();

                if($result){
                    $return->status = "200";
                    $return->msg = "success";
    
                }else{
                    $return->status = "500";
                    $return->msg = "fail";
                }

            }else{
                $return->status = "500";
                $return->msg = "fail";
                $return->reason = "권한이 없습니다." ;
            }            
            
        }
    
        echo(json_encode($return));    

    }



    public function image_update(Request $request)
    {
        //dd($request);
        $return = new \stdClass;

        $return->status = "500";
        $return->msg = "관리자에게 문의";
        $return->data = $request->name ;

        $login_user = Auth::user();
        $user_id = $login_user->getId();
        $user_type = $login_user->getType();

        /* 중복 체크 - start*/
        
        
        $id_cnt = User::where('id',$user_id)->count();
        
        if($id_cnt == 0 || $user_id == ""){// 아이디 존재여부
            $return->status = "601";
            $return->msg = "유효하지 않은 파트너 아이디 입니다.";
            $return->data = $request->name ;
        }elseif( $user_type == 0 ){//일반회원
            $return->status = "602";
            $return->msg = "일반 회원입니다.";
            $return->data = $request->name ;
        }else{

            $room_id = $request->room_id;
            $file_name = $request->file_name;
            $order_no = $request->order_no;

            $room_image_cnt = RoomImage::where('room_id',$room_id)->where('order_no', $order_no)->count();
            $result;
            $room_info = Room::where('id',$room_id)->first();

            $grant = Hotel::where('id',$room_info->hotel_id)->where('partner_id',$user_id)->count();

            if($grant){

                if($room_image_cnt){ // 해당 호텔 이미지가 있는 경우는 update
                    $result = RoomImage::where('room_id',$room_id)->where('order_no', $order_no)->update([
                        'room_id'=> $room_id,
                        'file_name'=> $file_name ,
                        'order_no'=> $order_no,
                        
                    ]);
                }else{
                    $result = RoomImage::insert([
                        'room_id'=> $room_id,
                        'file_name'=> $file_name ,
                        'order_no'=> $order_no,
                        'created_at' => Carbon::now()
                    ]);
                }
                
    
                if($result){
                    $return->status = "200";
                    $return->msg = "success";
    
                }else{
                    $return->status = "500";
                    $return->msg = "fail";
                }

            }else{
                $return->status = "500";
                $return->msg = "fail";
                $return->reason = "권한이 없습니다." ;
            }

        }
        

        echo(json_encode($return));    

    }

    public function image_delete(Request $request)
    {
        //dd($request);
        $return = new \stdClass;

        $return->status = "500";
        $return->msg = "관리자에게 문의";
        $return->data = $request->name ;

        $login_user = Auth::user();
        $user_id = $login_user->getId();
        $user_type = $login_user->getType();

        /* 중복 체크 - start*/
        
        
        $id_cnt = User::where('id',$user_id)->count();
        
        if($id_cnt == 0 || $user_id == ""){// 아이디 존재여부
            $return->status = "601";
            $return->msg = "유효하지 않은 파트너 아이디 입니다.";
            $return->data = $request->name ;
        }elseif( $user_type == 0 ){//일반회원
            $return->status = "602";
            $return->msg = "일반 회원입니다.";
            $return->data = $request->name ;
        }else{

            $room_id = $request->room_id;
            $file_name = $request->file_name;
            $order_no = $request->order_no;

            $room_image_cnt = RoomImage::where('room_id',$room_id)->where('order_no', $order_no)->count();
            $result;
            $room_info = Room::where('id',$room_id)->first();

            $grant = Hotel::where('id',$room_info->hotel_id)->where('partner_id',$user_id)->count();

            if($grant){
                $result = RoomImage::where('room_id',$room_id)->where('order_no', $order_no)->delete();

                if($result){
                    $return->status = "200";
                    $return->msg = "success";
                }else{
                    $return->status = "500";
                    $return->msg = "fail";    
                }
            }else{
                $return->status = "500";
                $return->msg = "fail";    
                $return->reason = "삭제 권한이 없습니다.";
            }

            
            
        }
        

        echo(json_encode($return));    

    }

    



}
