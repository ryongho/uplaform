<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Payment;
use App\Models\Reservation;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function regist(Request $request)
    {
        //dd($request);

        $login_user = Auth::user();

        $user_id = $login_user->id;

        $return = new \stdClass;

        
        $result = Payment::insert([
            'reservation_id'=> $request->reservation_id ,
            'user_id'=> $user_id ,
            'imp_uid'=> $request->imp_uid ,
            'pay_method'=> $request->pay_method ,
            'merchant_uid'=> $request->merchant_uid ,
            'name'=> $request->order_name ,
            'paid_amount'=> $request->paid_amount ,
            'currency'=> $request->currency ,
            'pg_provider'=> $request->pg_provider ,
            'pg_type'=> $request->pg_type ,
            'pg_tid'=> $request->pg_tid ,
            'apply_num'=> $request->apply_num ,
            'buyer_name'=> $request->buyer_name ,
            'buyer_tel'=> $request->buyer_tel ,
            'buyer_email'=> $request->buyer_email ,
            'buyer_addr'=> $request->buyer_addr ,
            'custom_data'=> $request->custom_data ,
            'paid_at'=> $request->paid_at ,
            'status'=> $request->status ,
            'receipt_url'=> $request->receipt_url ,
            'cpid'=> $request->cpid ,
            'data'=> $request->data ,
            'card_name'=> $request->card_name ,
            'bank_name'=> $request->bank_name ,
            'card_quota'=> $request->card_quota ,
            'card_number'=> $request->card_number ,
            'created_at' => Carbon::now(),
        ]);

        if($result){
            $return->status = "200";
            $return->msg = "success";
        }else{
            $return->status = "500";
            $return->msg = "fail";
        }

        return response()->json($return, 200)->withHeaders([
            'Content-Type' => 'application/json'
        ]);       

    }

    public function list_by_user_admin(Request $request){
  
        $user_id = $request->id;
        $page_no = $request->page_no;
        $row = $request->row;

        $offset = (($page_no-1) * $row);
    

        $return = new \stdClass;

        $rows = Payment::join('reservations', 'reservations.id', '=', 'payments.reservation_id')
                    ->select('payments.id as payment_id',
                            'reservations.reservation_no',
                            'payments.created_at as paid_at',
                            'reservations.reservation_type',
                            'reservations.services',
                            'reservations.price',
                            'payments.status',
                    )
                    ->where('payments.user_id',$user_id) 
                    ->offset($offset)
                    ->limit($row)
                    ->orderby('payments.id','desc')
                    ->get();

        $cnt = Payment::join('reservations', 'reservations.id', '=', 'payments.reservation_id')
                ->where('payments.user_id',$user_id)->count();
    
        $return->status = "200";
        $return->cnt = $cnt;
        $return->data = $rows;

        return response()->json($return, 200)->withHeaders([
            'Content-Type' => 'application/json'
        ]);

        
    }

    public function list_by_user(Request $request){
  
        $login_user = Auth::user();
        $user_id = $login_user->id;

        $start_no = $request->start_no;
        $row = $request->row;
    

        $return = new \stdClass;

        $rows = Payment::join('reservations', 'reservations.id', '=', 'payments.reservation_id')
                    ->select('payments.id as payment_id',
                            'payments.created_at as paid_at',
                            'reservations.reservation_type',
                            'reservations.services',
                            'reservations.price',
                            'payments.pay_method',
                            'payments.card_name',
                            'payments.card_number',
                            'reservations.status as reservation_status',
                    )
                    ->where('payments.id','>',$start_no)
                    ->where('payments.user_id',$user_id) 
                    ->orderby('payments.id','desc')
                    ->get();
    

        $return->status = "200";
        $return->cnt = count($rows);
        $return->data = $rows;

        return response()->json($return, 200)->withHeaders([
            'Content-Type' => 'application/json'
        ]);

        
    }

    public function list(Request $request){
  
        $start_date = $request->start_date;     
        $end_date = $request->end_date;
        $keyword = $request->keyword;
        $card_name = $request->card_name;
        $status = $request->status;
        $row = $request->row;
        
        $page_no = $request->page_no;
        $offset = (($page_no-1) * $row);

        $type = $request->type;

        $return = new \stdClass;

        $rows = Payment::join('users', 'users.id', '=', 'payments.user_id')
                    ->join('reservations', 'reservations.id', '=', 'payments.reservation_id')
                    ->select('payments.id as payment_id',
                            'payments.imp_uid as payment_no',
                            'merchant_uid',
                            'buyer_name',
                            'paid_at',
                            'price',
                            'card_name',
                            'reservations.reservation_type',
                            'payments.status',
                    )
                    ->when($card_name, function ($query, $card_name) {
                        return $query->where('payments.card_name', 'like', "%".$card_name."%");
                    })
                    ->when($status, function ($query, $status) {
                        if($status != "전체"){
                            return $query->where('payments.status', $status);
                        }
                    })
                    ->when($type, function ($query, $type) {
                        if($type != "전체"){
                            if($type == "고객명"){
                                return $query->where('payment.buyer_name', 'like', '%'.$keyword.'%');
                            }else{
                                return $query->where('reservations.reservation_type', $type);
                            }
                            
                        }
                        
                    })
                    ->whereBetween('payments.created_at',[$start_date.' 00:00:00',$end_date.' 23:59:59']) 
                    ->offset($offset)
                    ->limit($row)
                    ->orderby('payments.id','desc')
                    ->get();
        
        $cnt = Payment::join('users', 'users.id', '=', 'payments.user_id')
                    ->join('reservations', 'reservations.id', '=', 'payments.reservation_id')
                    ->when($card_name, function ($query, $card_name) {
                        return $query->where('payments.card_name', 'like', "%".$card_name."%");
                    })
                    ->when($status, function ($query, $status) {
                        if($status != "전체"){
                            return $query->where('payments.status', $status);
                        }
                    })
                    ->when($type, function ($query, $type) {
                        if($type != "전체"){
                            if($type == "고객명"){
                                return $query->where('payment.buyer_name', 'like', '%'.$keyword.'%');
                            }else{
                                return $query->where('reservations.reservation_type', $type);
                            }
                            
                        }
                        
                    })
                    ->whereBetween('payments.created_at',[$start_date.' 00:00:00',$end_date.' 23:59:59'])
                    ->count();

        $return->status = "200";
        $return->cnt = $cnt;
        $return->data = $rows;

        return response()->json($return, 200)->withHeaders([
            'Content-Type' => 'application/json'
        ]);

        
    }

    public function detail(Request $request){
  
        $payment_id = $request->payment_id;     
        
        $return = new \stdClass;
        
         
        $rows = Payment::join('users', 'users.id', '=', 'payments.user_id')
                    ->join('reservations', 'reservations.id', '=', 'payments.reservation_id')
                    ->select('payments.id as payment_id',
                            'payments.imp_uid as payment_no',
                            'merchant_uid',
                            'buyer_name',
                            'buyer_tel',
                            'users.gender',
                            'users.reg_no',
                            'paid_at',
                            'price',
                            'card_name',
                            'reservations.reservation_type',
                            'payments.status',
                            'reservations.canceled_at',
                            DB::raw('(select matched_at from applies where status = "S" and reservation_id = "reservations.id") as matched_at'),
                    )
                    ->where('payments.id',$payment_id)
                    ->first();


        $return->status = "200";
        $return->data = $rows;

        return response()->json($return, 200)->withHeaders([
            'Content-Type' => 'application/json'
        ]);

        
    }


    public function cancel(Request $request){
        
        $return = new \stdClass;

        $ids = explode(",",$request->payment_ids);

        $payment_cancel = Payment::whereIn('id', $ids)->update(['status' => 'refunded', 'refunded_at' => Carbon::now()]); // 환불

        $payment_ids =  payment::whereIn('id', $ids)->select('reservation_id')->get();
    
        $reservation_cancel = Reservation::whereIn('id', $payment_ids)->update(['status' => 'C', 'canceled_at' => Carbon::now()]); // 취소 
         
        if($reservation_cancel && $payment_cancel ){
            $return->status = "200";
            $return->msg = "success";
            
        }else{
            $return->status = "500";
            $return->msg = "success";
            
        }     

        return response()->json($return, 200)->withHeaders([
            'Content-Type' => 'application/json'
        ]);

        
    }


}
