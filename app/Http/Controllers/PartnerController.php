<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\PartnerInfo;
use App\Models\Apply;
use App\Models\Reservation;
use App\Models\Pay;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;


class PartnerController extends Controller
{
    public function regist(Request $request)
    {
        //dd($request);
        $return = new \stdClass;

        $return->status = "500";
        $return->msg = "관리자에게 문의";

        $result = PartnerInfo::insertGetId([
            'user_id'=> $request->user_id,
            'service_type'=> $request->service_type,
            'partner_type'=> $request->partner_type,
            'confirm_history'=> $request->confirm_history,
            'activity_distance'=> $request->activity_distance,
            'license_img'=> $request->license_img,
            'reg_img'=> $request->reg_img,
            'biz_type'=> $request->biz_type,
            'reg_no'=> $request->biz_reg_no,
            'biz_name'=> $request->biz_name,
            'address'=> $request->address,
            'address2'=> $request->address2 ,
            'ceo_name'=> $request->ceo_name,
            'tel'=> $request->tel,
            'position'=> $request->position,
            'job'=> $request->job,
            'created_at' => Carbon::now(),
        ]);

    
        if($result){
            $return->status = "200";
            $return->msg = "success";
        }
    
    
        return response()->json($return, 200)->withHeaders([
            'Content-Type' => 'application/json'
        ]);;

        //return view('user.profile', ['user' => User::findOrFail($id)]);
    }

    public function list(Request $request){
        $page_no = $request->page_no;
        $row = $request->row;
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $search_type = $request->search_type;
        $search_keyword = $request->search_keyword;

        $start_no = ($page_no - 1) * $row ;
        
        $rows = User::join('partner_infos', 'users.id', '=', 'partner_infos.user_id')
                ->select(
                    'users.id as user_id',
                    'partner_infos.id as partner_id',
                    'partner_infos.approval',
                    'partner_infos.approved_at',
                    'partner_infos.partner_type',
                    'users.email',
                    'users.sns_key',
                    'users.phone',
                    'users.name',
                    'users.gender',
                    'users.created_at',
                    'users.last_login',
                    'users.leave',
                )->where('users.id' ,">=", $start_no)
                ->where('users.user_type','1')
                ->where('users.created_at','>=',$start_date)
                ->where('users.created_at','<=',$end_date)
                ->where('users.name','like','%'.$search_keyword.'%')
                ->when($search_type, function ($query, $search_type) {
                    if($search_type == "정상"){
                        return $query->whereIn('users.leave', ['N']);
                    }else if($search_type == "탈퇴"){
                        return $query->whereIn('users.leave', ['Y']);
                    }else if($search_type == "승인대기"){
                        return $query->whereIn('partner_infos.approval', ['N']);
                    }
                })
                ->orderBy('users.id', 'desc')->limit($row)->get();
        
        $i = 0;
        foreach($rows as $row) {
            if($row['sns_key'] != ""){ // sns로그인인 경우
                $sns_keys = explode('_',$row['sns_key']);
                $rows[$i]['user_type'] = $sns_keys[0];
            }else{
                $rows[$i]['user_type'] = "유플랫폼";
            }
            //matching_cnt
            $rows[$i]['matching_cnt'] = Apply::where('user_id',$row['user_id'])->where('status','S')->count();
            
            //payment_cnt
            $rows[$i]['pay_cnt'] = Pay::where('user_id',$row['user_id'])->count();
            $i++;
        }

        $list = new \stdClass;

        $list->status = "200";
        $list->msg = "success";
        $list->cnt = count($rows);
        $list->data = $rows;
        
        
        return response()->json($list, 200)->withHeaders([
            'Content-Type' => 'application/json'
        ]);;
        
    }
    
    public function approve(Request $request){

        $user_id = $request->user_id;
        
        $return = new \stdClass;

        $return->status = "200";
        $return->msg = "변경 성공";
 
        $result = PartnerInfo::where('user_id', $user_id)
        ->update([
            'approval'=> 'Y' ,
            'approved_at'=>Carbon::now(),
        ]);
 

        if(!$result){
            $return->status = "500";
            $return->msg = "변경 실패";
        }

        return response()->json($return, 200)->withHeaders([
            'Content-Type' => 'application/json'
        ]);;

    }

    public function detail(Request $request){

        $user_id = $request->user_id;
        $list = new \stdClass;


        $rows = User::join('partner_infos', 'users.id', '=', 'partner_infos.user_id')
                ->select('users.id as user_id','users.email','users.name','users.phone','users.reg_no','users.gender','users.last_login','users.created_at','partner_infos.approval','partner_infos.approved_at')
                ->where('users.id',$user_id)->first();

        if($rows){

            if($rows['sns_key'] != ""){ // sns로그인인 경우
                $sns_keys = explode('_',$rows['sns_key']);
                $rows['user_type'] = $sns_keys[0];
            }else{
                $rows['user_type'] = "유플랫폼";
            }

            if($rows['leave'] == "Y"){ 
                $rows['status'] = "탈퇴";
            }else{
                $rows['status'] = "정상";
            }

            //matching_cnt
            $rows['matching_cnt'] = Apply::where('user_id',$user_id)->where('status','S')->count();


            $list->status = "200";
            $list->msg = "success";
            $list->data = $rows;
        }else{
            $list->status = "500";
            $list->msg = "해당 정보가 없습니다.";
        }
 
        return response()->json($list, 200)->withHeaders([
            'Content-Type' => 'application/json'
        ]);
        
    }

    public function info(Request $request){

        $user_id = $request->user_id;
        $list = new \stdClass;

        $rows = PartnerInfo::select('partner_type',
                                    'confirm_history',
                                    'license_img',
                                    'reg_img',
                                    'biz_type',
                                    'reg_no',
                                    'biz_name',
                                    'address',
                                    'address2',
                                    'updated_at',
                                    'activity_distance',
                                    'position',
                                    'ceo_name',
                                    'tel',

                            )
                ->where('user_id',$user_id)->first();

        if($rows){
            $list->status = "200";
            $list->msg = "success";
            $list->data = $rows;
        }else{
            $list->status = "500";
            $list->msg = "해당 정보가 없습니다.";
        }
 
        return response()->json($list, 200)->withHeaders([
            'Content-Type' => 'application/json'
        ]);
        
    }


    
}
