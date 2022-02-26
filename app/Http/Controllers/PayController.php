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
        $start_month = $request->start_month;
        $end_month = $request->end_month;

        $offset = (($page_no-1) * $row);

        $return = new \stdClass;


        $rows = Pay::join('reservations', 'reservations.id', '=', 'pays.reservation_id')
                    ->select(
                        DB::raw('DATE_FORMAT( pays.created_at, "%Y-%m" ) as month'),
                        DB::raw('count(distinct(pays.user_id)) as partner_cnt'),
                        DB::raw('count(CASE WHEN reservations.reservation_type="CS" THEN 1 END) as cs_cnt'),
                        DB::raw('count(CASE WHEN reservations.reservation_type="CR" THEN 1 END) as cr_cnt'),
                        DB::raw('count(CASE WHEN reservations.reservation_type="LC" THEN 1 END) as lc_cnt'),
                        DB::raw('count(*) as count'),
                        DB::raw('count(CASE WHEN pays.state="S" THEN 1 END) as success_cnt'),
                        DB::raw('count(CASE WHEN pays.state="W" THEN 1 END) as wait_cnt'),
                    )
                    ->where('pays.created_at','>=',$start_month."-01 00:00:00")
                    ->where('pays.created_at','<=',$end_month."-31 23:59:59")
                    ->groupBy('month')
                    ->orderby('month','desc')
                    ->offset($offset)
                    ->limit($row)
                    ->get();

        $cnt = Pay::select(
                        DB::raw('DATE_FORMAT( pays.created_at, "%Y-%m" ) as month'),
                    )   
                    ->where('pays.created_at','>=',$start_month."-01 00:00:00")
                    ->where('pays.created_at','<=',$end_month."-31 23:59:59")
                    ->groupBy('month')
                    ->get();

        $return->status = "200";
        $return->cnt = count($cnt);
        $return->data = $rows;

        return response()->json($return, 200)->withHeaders([
            'Content-Type' => 'application/json'
        ]);

        
    }

    public function list_type(Request $request){
  
        $year = $request->year;
        $month = $request->month;

        $return = new \stdClass;


        $rows = Pay::join('reservations', 'reservations.id', '=', 'pays.reservation_id')
                    ->select(
                        DB::raw('reservations.reservation_type'),
                        DB::raw('count(distinct(pays.user_id)) as partner_cnt'),
                        DB::raw('count(*) as count'),
                        DB::raw('count(CASE WHEN pays.state="S" THEN 1 END) as success_cnt'),
                        DB::raw('count(CASE WHEN pays.state="W" THEN 1 END) as wait_cnt'),
                        DB::raw('sum(reservations.price) as sum_price'),
                        DB::raw('sum(pays.amount) sum_amount'),
                    )
                    ->where('pays.created_at','>=',$year."-".$month."-01 00:00:00")
                    ->where('pays.created_at','<=',$year."-".$month."-31 23:59:59")
                    ->groupBy('reservations.reservation_type')
                    ->get();

        $total = Pay::join('reservations', 'reservations.id', '=', 'pays.reservation_id')
                ->select(
                    DB::raw('count(*) as count'),
                    DB::raw('sum(reservations.price) as sum_price'),
                    DB::raw('sum(pays.amount) sum_amount'),
                )
                ->where('pays.created_at','>=',$year."-".$month."-01 00:00:00")
                ->where('pays.created_at','<=',$year."-".$month."-31 23:59:59")
                ->get();

        $return->status = "200";
        $return->year = $year;
        $return->month = $month;
        $return->totla = $total;
        $return->data = $rows;
        

        return response()->json($return, 200)->withHeaders([
            'Content-Type' => 'application/json'
        ]);

        
    }

    public function list_partner(Request $request){
  
        $year = $request->year;
        $month = $request->month;
        $reservation_type = $request->reservation_type;

        $page_no = $request->page_no;
        $row = $request->row;

        $offset = (($page_no-1) * $row);

        $return = new \stdClass;


        $rows = Pay::join('reservations', 'reservations.id', '=', 'pays.reservation_id')
                    ->join('users', 'users.id', '=', 'pays.user_id')
                    ->select(
                        DB::raw('pays.user_id as user_id'),
                        DB::raw('users.name'),
                        DB::raw('count(*) as count'),
                        DB::raw('count(CASE WHEN pays.state="S" THEN 1 END) as success_cnt'),
                        DB::raw('count(CASE WHEN pays.state="W" THEN 1 END) as wait_cnt'),
                        DB::raw('sum(reservations.price) as sum_price'),
                        DB::raw('sum(pays.amount) sum_amount'),
                    )
                    ->where('pays.created_at','>=',$year."-".$month."-01 00:00:00")
                    ->where('pays.created_at','<=',$year."-".$month."-31 23:59:59")
                    ->where('reservation_type',$reservation_type)
                    ->offset($offset)
                    ->limit($row)
                    ->groupBy('pays.user_id')
                    ->get();
        
        $cnt = Pay::join('reservations', 'reservations.id', '=', 'pays.reservation_id')
                    ->join('users', 'users.id', '=', 'pays.user_id')
                    ->select(
                        DB::raw('pays.user_id as user_id'),
                        DB::raw('users.name'),
                        DB::raw('count(*) as count'),
                        DB::raw('count(CASE WHEN pays.state="S" THEN 1 END) as success_cnt'),
                        DB::raw('count(CASE WHEN pays.state="W" THEN 1 END) as wait_cnt'),
                        DB::raw('sum(reservations.price) as sum_price'),
                        DB::raw('sum(pays.amount) sum_amount'),
                    )
                    ->where('pays.created_at','>=',$year."-".$month."-01 00:00:00")
                    ->where('pays.created_at','<=',$year."-".$month."-31 23:59:59")
                    ->where('reservation_type',$reservation_type)
                    ->groupBy('pays.user_id')
                    ->get();


        $total = Pay::join('reservations', 'reservations.id', '=', 'pays.reservation_id')
                    ->select(
                        DB::raw('count(*) as count'),
                        DB::raw('sum(reservations.price) as sum_price'),
                        DB::raw('sum(pays.amount) sum_amount'),
                    )
                    ->where('pays.created_at','>=',$year."-".$month."-01 00:00:00")
                    ->where('pays.created_at','<=',$year."-".$month."-31 23:59:59")
                    ->where('reservation_type',$reservation_type)
                    ->get();

        $return->status = "200";
        $return->year = $year;
        $return->month = $month;
        $return->totla = $total;
        $return->cnt = count($cnt);
        $return->data = $rows;
        
        

        return response()->json($return, 200)->withHeaders([
            'Content-Type' => 'application/json'
        ]);

        
    }

    public function list_day(Request $request){
  
        $year = $request->year;
        $month = $request->month;
        $reservation_type = $request->reservation_type;

        $return = new \stdClass;

        $rows = Pay::join('reservations', 'reservations.id', '=', 'pays.reservation_id')
                    ->select(
                        DB::raw('DATE_FORMAT( pays.created_at, "%Y-%m-%d" ) as day'),
                        DB::raw('count(*) as count'),
                        DB::raw('count(CASE WHEN pays.state="S" THEN 1 END) as success_cnt'),
                        DB::raw('count(CASE WHEN pays.state="W" THEN 1 END) as wait_cnt'),
                        DB::raw('sum(reservations.price) as sum_price'),
                        DB::raw('sum(pays.amount) sum_amount'),
                        DB::raw('(sum(reservations.price) - sum(pays.amount)) as fee'),
                    )
                    ->where('pays.created_at','>=',$year."-".$month."-01 00:00:00")
                    ->where('pays.created_at','<=',$year."-".$month."-31 23:59:59")
                    ->groupBy('day')
                    ->orderBy('day','desc')
                    ->get();

        $total = Pay::join('reservations', 'reservations.id', '=', 'pays.reservation_id')
                ->select(
                    DB::raw('count(*) as count'),
                    DB::raw('sum(reservations.price) as sum_price'),
                    DB::raw('sum(pays.amount) sum_amount'),
                )
                ->where('pays.created_at','>=',$year."-".$month."-01 00:00:00")
                ->where('pays.created_at','<=',$year."-".$month."-31 23:59:59")
                ->get();

        $return->status = "200";
        $return->year = $year;
        $return->month = $month;
        $return->totla = $total;
        $return->data = $rows;
        
        

        return response()->json($return, 200)->withHeaders([
            'Content-Type' => 'application/json'
        ]);

        
    }

    public function list_by_date(Request $request){
  
        $date = $request->date;
        $reservation_type = $request->reservation_type;

        $return = new \stdClass;

        $rows = Pay::join('reservations', 'reservations.id', '=', 'pays.reservation_id')
                    ->select(
                        'pays.created_at',
                        DB::raw('(select count(*) from applies where reservation_id = reservations.id) as partner_cnt'),
                        'reservations.price',
                        'pays.amount',
                        DB::raw('(reservations.price - pays.amount) as fee'),
                    )
                    ->where('pays.created_at','>=', $date.' 00:00:00')
                    ->where('pays.created_at','<=', $date.' 23:59:59')
                    ->get();

        $total = Pay::join('reservations', 'reservations.id', '=', 'pays.reservation_id')
                ->select(
                    DB::raw('count(*) as count'),
                    DB::raw('sum(reservations.price) as sum_price'),
                    DB::raw('sum(pays.amount) sum_amount'),
                )
                ->where('pays.created_at','>=', $date.' 00:00:00')
                ->where('pays.created_at','<=', $date.' 23:59:59')
                ->get();

        $return->status = "200";
        $return->totla = $total;
        $return->data = $rows;
        
        

        return response()->json($return, 200)->withHeaders([
            'Content-Type' => 'application/json'
        ]);

        
    }

    public function list_payment(Request $request){
  
        $year = $request->year;
        $month = $request->month;
        $day = $request->day;
        $reservation_type = $request->reservation_type;
        $user_id = $request->user_id;

        $return = new \stdClass;

        $rows = Pay::join('reservations', 'reservations.id', '=', 'pays.reservation_id')
                    ->join('users', 'users.id', '=', 'pays.user_id')
                    ->select(
                        'pays.id as pay_id',
                        'pays.created_at',
                        'users.name',
                        'users.email',
                        'reservations.service_detail',
                        'reservations.service_date',
                        'reservations.service_time',
                        'reservations.finished_at',
                        'pays.state',    
                    )
                    ->where('pays.created_at','>=',$year."-".$month."-".$day." 00:00:00")
                    ->where('pays.created_at','<=',$year."-".$month."-".$day." 23:59:59")
                    ->where('pays.user_id',$user_id)
                    ->get();

        $return->status = "200";
        $return->data = $rows;

        return response()->json($return, 200)->withHeaders([
            'Content-Type' => 'application/json'
        ]);

        
    }

    public function pay(Request $request){
  
        $pay_id = $request->pay_id;
        
        $return = new \stdClass;

        $result = Pay::where('id', $pay_id)->update(['state' => 'S', 'paid_at' => Carbon::now()]); // 정산완료처리
        
        if($result){
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
