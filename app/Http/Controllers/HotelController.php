<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Hotel;
use App\Models\HotelImage;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class HotelController extends Controller
{
    public function regist(Request $request)
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

            


            $result = Hotel::insertGetId([
                'partner_id'=> $login_user->id ,
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
                'latitude' => $request->latitude ,
                'longtitude' => $request->longtitude ,
                'type' => $request->type ,
                'parking' => $request->parking ,
                'refund_rule' => $request->refund_rule ,
                'options' => $request->options ,
                'created_at' => Carbon::now()
            ]);

            $no = 1;
            $images = explode(",",$request->images);
            foreach( $images as $image){
            
                $result_img = HotelImage::insertGetId([
                    'hotel_id'=> $result ,
                    'file_name'=> $image ,
                    'order_no'=> $no ,
                    'created_at' => Carbon::now()
                ]);

                $no++;
            }
            

            if($result){
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

        $rows = Hotel::where('id','>=',$s_no)->orderBy('id', 'desc')->limit($row)->get();

        $return = new \stdClass;

        $return->status = "200";
        $return->cnt = count($rows);
        $return->data = $rows ;

        echo(json_encode($return));

    }

    public function list_by_partner(Request $request){

        $login_user = Auth::user();

        $rows = Hotel::where('partner_id',$login_user->id)->get();

        $return = new \stdClass;

        $return->status = "200";
        $return->cnt = count($rows);
        $return->data = $rows ;

        echo(json_encode($return));

    }

    public function detail(Request $request){
        $id = $request->id;

        $rows = Hotel::where('id','=',$id)->get();
        $images = HotelImage::where('hotel_id','=',$id)->orderBy('order_no')->get();

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

            $result = Hotel::where('id',$request->id)->update([
                'partner_id'=> $login_user->id ,
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
                'latitude' => $request->latitude ,
                'longtitude' => $request->longtitude ,
                'type' => $request->type ,
                'parking' => $request->parking ,
                'refund_rule' => $request->refund_rule ,
                'options' => $request->options ,
            ]);
            

            if($result){
                $return->status = "200";
                $return->msg = "success";
                $return->updated_id = $result ;

            }
            
        }
        

        echo(json_encode($return));    

    }

    

}
