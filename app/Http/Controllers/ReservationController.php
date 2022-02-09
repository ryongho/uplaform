<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\Reservation;
use App\Models\Service;
use App\Models\Apply;
use App\Models\User;
use App\Models\PartnerInfo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class ReservationController extends Controller
{
    public function regist(Request $request)
    {
    
        $return = new \stdClass;

        $return->status = "500";
        $return->msg = "관리자에게 문의";
        

        $login_user = Auth::user();
        $user_id = $login_user->getId();

        $now = date('ymdHis');
        
        $reservation_no = "R_".$now."_".$user_id;

        $service_arr = explode(",",$request->services);
        $services = Service::whereIn('id',$service_arr )->get();
        $service_detail = "";

        foreach($services as $service){
            $service_detail .= $service->service_name." (".number_format($service->price)."원),";
        }

        if(isset($services )){
            $result = Reservation::insertGetId([
                'user_id' => $user_id,
                'reservation_type'=> $request->reservation_type ,
                'reservation_no'=> $reservation_no ,
                'services'=> $request->services ,
                'service_detail'=> $service_detail ,
                'service_date'=> $request->service_date ,
                'service_time'=> $request->service_time ,
                'service_addr'=> $request->service_addr ,
                'phone'=> $request->phone ,
                'memo'=> $request->memo ,
                'price'=> $request->price ,
                'learn_day'=> $request->learn_day ,
                'status'=> "W" ,
                'created_at'=> Carbon::now(),
            ]);

        }else{
            $result =  0;
        }
            
        

        if($result){ //DB 입력 성공

            $return->status = "200";
            $return->msg = "success";
            $return->insert_id = $result ;
            
        }else{
            $return->status = "500";
            $return->msg = "fail";
        }

        return response()->json($return, 200)->withHeaders([
            'Content-Type' => 'application/json'
        ]);;
    }

    public function list(Request $request){

        $page_no = $request->page_no;
        $row = $request->row;
        $type = $request->type;
        $reservation_type = $request->reservation_type;
        $status = $request->status;
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $search_type = $request->search_type;
        $search_keyword = $request->search_keyword;
        $offset = (($page_no-1) * $row);



        if($search_type == "phone"){
            $search_type = "users.phone";
        }

        $search = new \stdClass;

        $search->type = $search_type;
        $search->keyword = $search_keyword;



        $rows = Reservation::join('users', 'users.id', '=', 'reservations.user_id')
                        ->select(   
                                'reservations.id as reservation_id',
                                'reservations.reservation_type',
                                'reservations.service_date',
                                'reservations.service_time',
                                'reservations.learn_day',
                                'reservations.service_detail',
                                'reservations.status',
                                'reservations.memo',
                                'reservations.price',
                                'reservations.created_at as created_at',
                                'reservations.reservation_no',
                                'reservations.service_addr',
                                'users.name as name',
                                'users.email as email',  
                                DB::raw('(select count(*) from applies where applies.reservation_id = reservations.id) as apply_cnt'),
                        )         
                        ->where('reservations.reservation_type' , $reservation_type)
                        ->when($type, function ($query, $type) {
                            if($type == "W"){//확정대기
                                return $query->whereIn('reservations.status', ['W','R','C']);
                            }else if($type == "I"){//진행중
                                return $query->whereIn('reservations.status', ['R']);
                            }else if($type == "S"){//완료
                                return $query->whereIn('reservations.status', ['S']);
                            }
                            
                        })
                        ->when($status, function ($query, $status) {    
                            return $query->where('reservations.status', $status);
                        })
                        ->where('reservations.created_at','>=', $start_date)
                        ->where('reservations.created_at','<=', $end_date.' 23:59:59')
                        ->when($search, function ($query, $search) {    
                            if($search->type != ""){
                                return $query->where( $search->type, 'like', "%".$search->keyword."%");
                            }
                        })
                        ->offset($offset)
                        ->limit($row)->get();

        $cnt = Reservation::join('users', 'users.id', '=', 'reservations.user_id')        
                        ->where('reservations.reservation_type' , $reservation_type)
                        ->when($type, function ($query, $type) {
                            if($type == "W"){//확정대기
                                return $query->whereIn('reservations.status', ['W','R','C']);
                            }else if($type == "I"){//진행중
                                return $query->whereIn('reservations.status', ['R']);
                            }else if($type == "S"){//완료
                                return $query->whereIn('reservations.status', ['S']);
                            }
                            
                        })
                        ->when($status, function ($query, $status) {    
                            return $query->where('reservations.status', $status);
                        })
                        ->where('reservations.created_at','>=', $start_date)
                        ->where('reservations.created_at','<=', $end_date.' 23:59:59')
                        ->when($search, function ($query, $search) {    
                            if($search->type != ""){
                                return $query->where( $search->type, 'like', "%".$search->keyword."%");
                            }
                        })
                        ->count();
        
        if($type == "I"){ //타입이 진행중인경우 추가 리턴
            $x = 0;
            foreach($rows as $row){
                $app_info = Apply::where('reservation_id', $row['reservation_id'])->where('status', 'S')->first();
                $user_info = PartnerInfos::where('user_id',$app_info['id'])->fitst();
                $rows[$x]['matched_name'] = $user_info['ceo_name'];
                $rows[$x]['phone'] = $user_info['tel'];
                $rows[$x]['address'] = $user_info['address'];
                $x++;
            }
        }

        $return = new \stdClass;

        $return->status = "200";
        $return->cnt = $cnt;

        $return->data = $rows ;

        return response()->json($return, 200)->withHeaders([
            'Content-Type' => 'application/json'
        ]);

    }
    public function list_cnt(Request $request){

        $w_cnt = Reservation::join('users', 'users.id', '=', 'reservations.user_id')
                            ->select(   
                                    'reservations.id as reservation_id',
                                    'reservations.reservation_type',
                                    'reservations.service_date',
                                    'reservations.service_time',
                                    'reservations.learn_day',
                                    'reservations.status',    
                            )         
                            ->where('reservations.reservation_type' , $request->reservation_type)
                            ->whereIn('reservations.status', ['W','C'])
                            ->count();

        $i_cnt = Reservation::join('users', 'users.id', '=', 'reservations.user_id')
                            ->select(   
                                    'reservations.id as reservation_id',
                                    'reservations.reservation_type',
                                    'reservations.service_date',
                                    'reservations.service_time',
                                    'reservations.learn_day',
                                    'reservations.status',    
                            )         
                            ->where('reservations.reservation_type' , $request->reservation_type)
                            ->whereIn('reservations.status', ['R'])
                            ->count();
        
        $s_cnt = Reservation::join('users', 'users.id', '=', 'reservations.user_id')
                            ->select(   
                                    'reservations.id as reservation_id',
                                    'reservations.reservation_type',
                                    'reservations.service_date',
                                    'reservations.service_time',
                                    'reservations.learn_day',
                                    'reservations.status',    
                            )         
                            ->where('reservations.reservation_type' , $request->reservation_type)
                            ->whereIn('reservations.status', ['S'])
                            ->count(); 

        $return = new \stdClass;

        $return->status = "200";

        $return->w_cnt = $w_cnt;
        $return->i_cnt = $i_cnt;
        $return->s_cnt = $s_cnt;

        return response()->json($return, 200)->withHeaders([
            'Content-Type' => 'application/json'
        ]);
    }

    public function list_by_user(Request $request){

        $login_user = Auth::user();
        $user_id = $login_user->id;

        $s_no = $request->start_no;
        $row = $request->row;
        $type = $request->type;

        $rows = Reservation::select(   
                                'id as reservation_id',
                                'reservation_type',
                                'service_date',
                                'service_time',
                                'learn_day',
                                'status',    
                        )         
                        ->where('id' ,">", $s_no)
                        ->where('user_id', $user_id)
                        ->when($type, function ($query, $type) {
                            if($type == "ing"){
                                return $query->whereIn('status', ['W','R']);
                            }else if($type == "end"){
                                return $query->whereIn('status', ['S','C']);
                            }
                            
                        })
                        ->limit($row)->get();

        $cnt = Reservation::where('user_id', $user_id)
            ->when($type, function ($query, $type) {
                if($type == "ing"){
                    return $query->whereIn('status', ['W','R']);
                }else if($type == "end"){
                    return $query->whereIn('status', ['S','C']);
                }
            
        })->count();

        $return = new \stdClass;

        $return->status = "200";
        $return->cnt = $cnt;
        $return->data = $rows ;

        return response()->json($return, 200)->withHeaders([
            'Content-Type' => 'application/json'
        ]);

    }

    
    public function detail(Request $request){
    
        $id = $request->reservation_id;

        $rows = Reservation::select(   
                                'id as reservation_id',
                                'reservation_type',
                                'service_date',
                                'service_time',
                                'services',
                                'status',
                                'reservation_no',    
                                'service_addr',
                                'memo',
                                'phone',
                                'service_detail',
                                'learn_day',
                                'price',
                                'created_at',
                                'finished_at',
                                'canceled_at',
                                'cancel_comment',
                                DB::raw('(select service_comment from applies where reservation_id = reservations.id) as service_comment'),
                        )         
                        ->where('id' , $id)
                        ->first();

        $return = new \stdClass;

        $return->status = "200";
        $return->data = $rows ;

        return response()->json($return, 200)->withHeaders([
            'Content-Type' => 'application/json'
        ]);


    }

    public function reqeust_list(Request $request){

        $return = new \stdClass;
        $login_user = Auth::user();
        $user_id = $login_user->id;

        $s_no = $request->start_no;
        $row = $request->row;
        $type = $request->type;

        $user_info = User::where('id',$user_id)->first();
        $partner_info = PartnerInfo::where('user_id',$user_id)->first();
        $partner_type = $partner_info['partner_type'];
        $addr = "";

        $rows = array();        

        if(isset($partner_info['address']) && $partner_info['address'] != ""){
            $addrs = explode(' ',$partner_info['address']);
            $addr = $addrs[0].' '.$addrs[1];
            
            $flag = new \stdClass;

            $flag->type = $type;
            $flag->addr = $addr;

            $rows = Reservation::select(

                'id as reservation_id',
                'reservation_type',
                'service_date',
                'service_time',
                'learn_day',    
                DB::raw('(select count(*) from applies where reservation_id = reservations.id) as apply_cnt'),
                DB::raw('(select count(*) from applies where reservation_id = reservations.id and user_id = '.$user_id.') as applied'),
                DB::raw('(select id from applies where reservation_id = reservations.id and user_id = '.$user_id.') as apply_id'),
                'service_addr'
            )         
            ->where('id' ,">", $s_no)
            ->where('status', 'W')
            ->where('reservation_type', $partner_type)
            ->when($flag, function ($query, $flag) {
                if($flag->type == "local"){
                    return $query->where('service_addr', 'like', "%".$flag->addr."%");
                }
                
            })
            ->limit($row)->get();

            $cnt = Reservation::where('status', 'W')
                ->where('reservation_type', $partner_type)
                ->when($flag, function ($query, $flag) {
                    if($flag->type == "local"){
                        return $query->where('service_addr', 'like', "%".$flag->addr."%");
                    }
                    
                })->count();
            $i = 0;

            foreach($rows as $row){
                $service_addrs = explode(' ',$row['service_addr']);
                $rows[$i]['service_addr'] = $service_addrs[0].' '.$service_addrs[1];
                if($row['applied'] > 0){
                    $rows[$i]['applied'] = "Y";
                }else{
                    $rows[$i]['applied'] = "N";
                }
                $i++;
            }
            $return->cnt = $cnt;
        }

        $return->status = "200";
        
        $return->data = $rows ;

        return response()->json($return, 200)->withHeaders([
            'Content-Type' => 'application/json'
        ]);

    }

    public function request_detail(Request $request){
    
        $login_user = Auth::user();
        $user_id = $login_user->id;

        $id = $request->reservation_id;

        $rows = Reservation::select(   
                                'id as reservation_id',
                                'reservation_type',
                                'service_date',
                                'service_time',
                                'services',
                                'status',
                                'reservation_no',    
                                'service_addr',
                                'memo',
                                'phone',
                                'service_detail',
                                'learn_day',
                                'price',
                                'created_at',
                                'finished_at',
                                DB::raw('(select count(*) from applies where reservation_id = reservations.id) as apply_cnt'),
                                DB::raw('(select count(*) from applies where reservation_id = reservations.id and user_id = '.$user_id.') as applied'),
                                DB::raw('(select created_at from applies where reservation_id = reservations.id and user_id = '.$user_id.') as applied_at'),
                                DB::raw('(select id from applies where reservation_id = reservations.id and user_id = '.$user_id.') as apply_id'),
                        )         
                        ->where('id' , $id)
                        ->first();

        
        if($rows['applied'] > 0){
            $rows['applied'] = "Y";
        }else{
            $rows['applied'] = "N";
        }
        
        
        $return = new \stdClass;

        $return->status = "200";
        $return->data = $rows ;

        return response()->json($return, 200)->withHeaders([
            'Content-Type' => 'application/json'
        ]);


    }

    public function cancel(Request $request){
        //dd($request);
        $return = new \stdClass;
        $login_user = Auth::user();


        $user_id = $login_user->id;

        $reservation_info = Reservation::where('id', $request->reservation_id)->first();

        if(!$reservation_info){
            $return->status = "601";
            $return->msg = "유효한 예약 정보가 아닙니다.";
            $return->reservation_id = $request->id;
        }else if($reservation_info->status == "C"){
            $return->status = "602";
            $return->msg = "이미 취소 처리된 예약입니다.";
            $return->reservation_id = $request->id;
        }else{
            
            $result = Reservation::where('id', $request->reservation_id)
                    ->update(['status' => 'C','cancel_comment' => $request->comment, 'canceled_at' => Carbon::now()]); // 취소 
            
            if(!$result){
                $return->status = "500";
                $return->msg = "취소처리 실패 실패";
            }else{
                $return->status = "200";
                $return->msg = "취소 완료";
            }
        }

    
        return response()->json($return, 200)->withHeaders([
            'Content-Type' => 'application/json'
        ]);;

    }

    

    public function update(Request $request){
        //dd($request);
        $return = new \stdClass;

        $return->status = "200";
        $return->msg = "변경 완료";
        $return->key = $request->key;
        $return->value = $request->value;
        $return->updated_id = $request->id;

        $login_user = Auth::user();
        $user_id = $login_user->id;

        $key = $request->key;
        $value = $request->value;
        $id = $request->id;

        $result = Reservation::where('id', $id)->update([$key => $value, 'update_user' => $user_id]);

        if(!$result){
            $return->status = "500";
            $return->msg = "변경 실패";
        }

        return response()->json($return, 200)->withHeaders([
            'Content-Type' => 'application/json'
        ]);;

    }

    public function delete(Request $request)
    {
        $return = new \stdClass;        
    
        $id = $request->reservation_id;
        $result = Reservation::where('id',$id)->delete();

        if($result){
            $return->status = "200";
            $return->msg = "success";

        }else{
            $return->status = "500";
            $return->msg = "fail";
        }

        echo(json_encode($return));    

    }

    



}
