<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Reservation;
use App\Models\Pay;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class PayController extends Controller
{

    public function list_by_user(Request $request){
  
        $login_user = Auth::user();
        $user_id = $login_user->id;

        $start_no = $request->start_no;
        $row = $request->row;

        $return = new \stdClass;

        $rows = Pay::join('reservations', 'reservations.id', '=', 'pays.reservation_id')
                    ->select(
                        DB::raw('DATE_FORMAT( pays.created_at, "%Y-%m" ) as month'),
                        DB::raw('count(*) as count'),
                        DB::raw('sum(amount) as amount'),
                        DB::raw('sum(CASE  
                        WHEN state = \'S\' THEN amount 
                            ELSE 0 
                        END)  as paid_amount'),
                    )
                    ->where('pays.id','>',$start_no)
                    ->where('pays.user_id',$user_id) 
                    ->groupBy('month')
                    ->orderby('month','desc')
                    ->get();
    

        $return->status = "200";
        $return->cnt = count($rows);
        $return->data = $rows;

        return response()->json($return, 200)->withHeaders([
            'Content-Type' => 'application/json'
        ]);

        
    }

    public function list_by_partner_admin(Request $request){
  
        $user_id = $request->id;
        $page_no = $request->page_no;
        $row = $request->row;

        $offset = (($page_no-1) * $row);

        $return = new \stdClass;

        $rows = Pay::join('reservations', 'reservations.id', '=', 'pays.reservation_id')
                    ->select(
                        'pays.created_at as created_at',
                        'reservations.reservation_type',
                        'reservations.price',
                        'pays.amount',
                        'pays.state',
                    )
                    ->where('pays.user_id',$user_id) 
                    ->offset($offset)
                    ->orderBy('pays.created_at')
                    ->limit($row)
                    ->get();
    

        $return->status = "200";
        $return->cnt = count($rows);
        $return->data = $rows;

        return response()->json($return, 200)->withHeaders([
            'Content-Type' => 'application/json'
        ]);

        
    }



    public function detail(Request $request){
  
        $month = $request->month;
        $login_user = Auth::user();
        $user_id = $login_user->id;
        $start_no = $request->start_no;
        $row = $request->row;     
        
        $return = new \stdClass;

        $total_row = Pay::select(
                        DB::raw('DATE_FORMAT( pays.created_at, "%Y-%m" ) as month'),
                        DB::raw('count(*) as count'),
                        DB::raw('sum(amount) as amount'),
                        DB::raw('sum(CASE  
                        WHEN state = \'S\' THEN amount 
                            ELSE 0 
                        END)  as paid_amount'),
                    )
                    ->where('pays.created_at','like', $month."%")
                    ->where('pays.user_id',$user_id) 
                    ->groupBy('month')
                    ->first();

        $return->total_amount = $total_row['amount'];
        $return->paid_amount = $total_row['paid_amount'];
        $return->count = $total_row['count'];
        
    
        $rows = Pay::join('reservations', 'reservations.id', '=', 'pays.reservation_id')
                    ->select(
                        DB::raw('DATE_FORMAT( pays.created_at, "%Y-%m-%d" ) as date'),
                        'reservations.reservation_no',
                        'reservations.reservation_type',
                        'pays.amount',
                        'reservations.price',
                        'pays.state',
                    )
                    ->where('pays.id','>',$start_no)
                    ->where('pays.created_at','like', $month."%")
                    ->where('pays.user_id',$user_id) 
                    ->orderby('pays.created_at','desc')
                    ->limit($row)
                    ->get();


        $return->status = "200";
        $return->data = $rows;

        return response()->json($return, 200)->withHeaders([
            'Content-Type' => 'application/json'
        ]);

        
    }


    public function list(Request $request){
  
        $page_no = $request->page_no;
        $row = $request->row;

        $offset = (($page_no-1) * $row);

        $return = new \stdClass;


        $rows = Pay::join('reservations', 'reservations.id', '=', 'pays.reservation_id')
                    ->select(
                        DB::raw('DATE_FORMAT( pays.created_at, "%Y-%m" ) as month'),
                        DB::raw('count(distinct("pays.user_id")) as partner_cnt'),
                        DB::raw('count(case when reservations.reservation_type="CS" than 1 end) as cs_cnt'),
                        DB::raw('count(case when reservations.reservation_type="CR" than 1 end) as cr_cnt'),
                        DB::raw('count(case when reservations.reservation_type="LC" than 1 end) as lc_cnt'),
                        DB::raw('count(*) as count'),
                        DB::raw('pays.state'),
                    )
                    ->groupBy('month')
                    ->orderby('month','desc')
                    ->offset($offset)
                    ->limit($row)
                    ->get();

    

        $return->status = "200";
        $return->cnt = count($rows);
        $return->data = $rows;

        return response()->json($return, 200)->withHeaders([
            'Content-Type' => 'application/json'
        ]);

        
    }


}
