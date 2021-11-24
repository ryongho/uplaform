<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Goods;
use App\Models\Quantity;
use App\Models\Hotel;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class QuantityController extends Controller
{
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

            $goods_info = Goods::where('id',$request->goods_id)->first();

            $grant = Hotel::where('id',$goods_info->hotel_id)->where('partner_id',$user_id)->count();
        
            if($grant){

                $grant = Quantity::where('goods_id',$request->goods_id)->where('date',$request->date)->count();
                $result;

                if($grant){
                    $result = Quantity::where('goods_id',$request->goods_id)->where('date',$request->date)->update([
                        'qty'=> $request->qty 
                    ]);
                }else{
                    $result = Quantity::insert([
                        'goods_id'=> $request->goods_id,
                        'date'=> $request->date,
                        'qty'=> $request->qty,
                        'created_at'=> Carbon::now(),
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





}
