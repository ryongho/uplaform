<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Payment;
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
            'imp_uid'=> $request->imp_uid ,
            'merchant_uid'=> $request->merchant_uid ,
            'order_name'=> $request->order_name ,
            'user_id'=> $user_id ,
            'price'=> $request->price ,
            'pay_type'=> $request->pay_type ,
            'pg'=> $request->pg ,
            'pg_orderno'=> $request->pg_orderno ,
            'detail'=> $request->detail ,
            'payed_at'=> $request->payed_at ,
            'status'=> $request->status ,
            'buyer_name'=> $request->buyer_name ,
            'buyer_phone'=> $request->buyer_phone ,
            'buyer_email'=> $request->buyer_email ,
            'buyer_addr'=> $request->buyer_addr ,
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

    public function list_by_user(Request $request){
  
        $login_user = Auth::user();
        $user_id = $login_user->id;

        $start_no = $request->start_no;
        $row = $request->row;
    

        $return = new \stdClass;

        $rows = Payment::join('reservations', 'reservations.id', '=', 'payments.reservation_id')
                    ->select('payments.id as payment_id',
                            'payed_at',
                            'reservations.reservation_type',
                            'reservations.services',
                            'reservations.price',
                            'payments.pay_type',
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
        $status = $request->status;
        
        $page_no = $request->page_no;
        $start_no = ($page_no - 1) * 30 ;

        $type = $request->type;

        $return = new \stdClass;

        $rows = Payment::join('users', 'users.id', '=', 'payments.user_id')
                    ->select('payments.id as payment_id','status','pg','pg_orderno',
                    DB::raw('(select apply_code from applies where id = payments.apply_id ) as apply_code'),
                    'buyer_name','buyer_phone','users.user_type','buyer_email','pay_type','payed_at','status','price')
                    ->when($keyword, function ($query, $keyword) {
                        return $query->where('users.name', 'like', "%".$keyword."%");
                    })
                    ->when($status, function ($query, $status) {
                        return $query->where('status', $status);
                    })
                    ->whereBetween('payments.created_at',[$start_date.' 00:00:00',$end_date.' 23:59:59']) 
                    ->where('payments.id','>',$start_no) 
                    ->orderby('payments.id','desc')
                    ->get();
    

        $return->status = "200";
        $return->cnt = count($rows);
        $return->data = $rows;

        return response()->json($return, 200)->withHeaders([
            'Content-Type' => 'application/json'
        ]);

        
    }

    public function detail(Request $request){
  
        $payment_id = $request->payment_id;     
        
        $return = new \stdClass;
        
         
        $rows = Payment::select('status','pg','pg_orderno','apply_id','buyer_name','buyer_phone','buyer_email','pay_type','payed_at','status','price')
                    ->where('id',$payment_id) 
                    ->first();


        $return->status = "200";
        $return->data = $rows;

        return response()->json($return, 200)->withHeaders([
            'Content-Type' => 'application/json'
        ]);

        
    }
}
