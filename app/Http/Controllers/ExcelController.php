<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Payment;
use App\Models\AreaInfo;
use App\Models\Reservation;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
use Illuminate\Support\Facades\DB;

//use Maatwebsite\Excel\Concerns\FromQuery;
//use Maatwebsite\Excel\Concerns\Exportable;
//use Maatwebsite\Excel\Facades\Excel;
use PHPExcel; 
use PHPExcel_IOFactory;
//use App\Exports\UserList;

class ExcelController extends Controller
{
    
    public function user_list(Request $request){
        ob_start();
        
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $search_type = $request->search_type;
        $search_keyword = $request->search_keyword;

        $rows = User::select(
                    'id',
                    'email',
                    'phone',
                    'name',
                    'sns_key',
                    'gender',
                    'created_at',
                    'last_login',
                    'leave',
                )
                ->where('user_type','0')
                ->where('created_at','>=',$start_date)
                ->where('created_at','<=',$end_date)
                ->where('name','like','%'.$search_keyword.'%')
                ->when($search_type, function ($query, $search_type) {
                    if($search_type == "정상"){
                        return $query->whereIn('leave', ['N']);
                    }else if($search_type == "탈퇴"){
                        return $query->whereIn('leave', ['Y']);
                    }else if($search_type == "삭제"){
                        return $query->whereIn('leave', []);
                    }
                })
                ->orderBy('id', 'desc')->get();
        dd($rows);
        $i = 0;
        foreach($rows as $row) {
            if($row['sns_key'] != ""){ // sns로그인인 경우
                $sns_keys = explode('_',$row['sns_key']);
                $rows[$i]['user_type'] = $sns_keys[0];
            }else{
                $rows[$i]['user_type'] = "유플랫폼";
            }
            //add_info
            $area_cnt = AreaInfo::where('user_id',$row['id'])->count();
            if($area_cnt){
                $rows[$i]['add_info'] = "Y";
            }else{
                $rows[$i]['add_info'] = "N";
            }

            if($row['leave'] == "Y"){ 
                $rows[$i]['status'] = "탈퇴";
            }else{
                $rows[$i]['status'] = "정상";
            }
            
            //reservation_cnt
            $rows[$i]['reservation_cnt'] = Reservation::where('user_id',$row['id'])->count();
            //payment_cnt
            $rows[$i]['payment_cnt'] = Payment::where('user_id',$row['id'])->count();
            $i++;
        }


        error_reporting(E_ALL);
        ini_set('display_errors', TRUE);
        ini_set('display_startup_errors', TRUE);
        date_default_timezone_set('Asia/Seoul');

        if (PHP_SAPI == 'cli')
            die('This example should only be run from a Web Browser');

        set_time_limit(120); 
        ini_set("memory_limit", "256M");

        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();

        // Set document properties
        $objPHPExcel->getProperties()->setCreator("Maarten Balliauw")
                                    ->setLastModifiedBy("Maarten Balliauw")
                                    ->setTitle("Office 2007 XLSX Test Document")
                                    ->setSubject("Office 2007 XLSX Test Document")
                                    ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
                                    ->setKeywords("office 2007 openxml php")
                                    ->setCategory("Test result file");


        // Add some data
        $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A1', '번호')
                    ->setCellValue('B1', '이메일(아이디)')
                    ->setCellValue('C1', '휴대폰번호')
                    ->setCellValue('D1', '이름')
                    ->setCellValue('E1', '회원유형')
                    ->setCellValue('F1', '성별')
                    ->setCellValue('G1', '추가정보')
                    ->setCellValue('H1', '신청내역')
                    ->setCellValue('I1', '결제내역')
                    ->setCellValue('J1', '가입일')
                    ->setCellValue('K1', '최근로그인')
                    ->setCellValue('L1', '상태');
        $i = 2;
        foreach ($rows as $row){

            $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A'.$i, $row['id'])
                        ->setCellValue('B'.$i, $row['email'])
                        ->setCellValue('C'.$i, $row['phone'])
                        ->setCellValue('D'.$i, $row['name'])
                        ->setCellValue('E'.$i, $row['user_type'])
                        ->setCellValue('F'.$i, $row['gender'])
                        ->setCellValue('G'.$i, $row['add_info'])
                        ->setCellValue('H'.$i, $row['reservation_cnt'])
                        ->setCellValue('I'.$i, $row['payment_cnt'])
                        ->setCellValue('J'.$i, $row['created_at'])
                        ->setCellValue('K'.$i, $row['last_login'])
                        ->setCellValue('L'.$i, $row['leave']);
            $i++;
        }
                                
        // Rename worksheet
        $objPHPExcel->getActiveSheet()->setTitle('user_list');


        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);


        // Redirect output to a client’s web browser (Excel2007)
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="user_list.xlsx"');
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');

        // If you're serving to IE over SSL, then the following may be needed
        header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
        header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header ('Pragma: public'); // HTTP/1.0

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;

        
    }

    public function payment_list(Request $request){
        ob_start();
        $start_date = $request->start_date;     
        $end_date = $request->end_date;
        $keyword = $request->keyword;
        $status = $request->status;
        
        $rows = Payment::join('users', 'users.id', '=', 'payments.user_id')
                    ->select('payments.id as payment_id','status','pg','pg_orderno',
                        DB::raw('(select apply_code from applies where id = payments.apply_id ) as apply_code'),
                        'buyer_name','buyer_phone','users.user_type as user_type','buyer_email','pay_type','payed_at','price')
                    ->when($keyword, function ($query, $keyword) {
                        return $query->where('users.name', 'like', "%".$keyword."%");
                    })
                    ->when($status, function ($query, $status) {
                        return $query->where('status', $status);
                    })
                    ->whereBetween('payments.created_at',[$start_date.' 00:00:00',$end_date.' 23:59:59']) 
                    ->orderby('payments.id','desc')
                    ->get();
        
        $list = array();
        $i = 0;

        foreach($rows as $row){
            
            $list[$i]['payment_id'] = $row->payment_id;
            $list[$i]['status'] = $row->status;
            $list[$i]['pg'] = $row->pg;
            $list[$i]['pg_orderno'] = $row->pg_orderno;
            $list[$i]['apply_code'] = $row->apply_code;
            $list[$i]['buyer_name'] = $row->buyer_name;
            $list[$i]['buyer_phone'] = $row->buyer_phone;
            $list[$i]['buyer_email'] = $row->buyer_email;
            $list[$i]['pay_type'] = $row->pay_type;
            $list[$i]['payed_at'] = $row->payed_at;
            $list[$i]['price'] = $row->price;


            if($row->user_type == 0){ // 일반회원
                $list[$i]['user_type'] = "일반회원";
            }else{
                $list[$i]['user_type'] = "기업회원";
            }
           
            $i++;
        }
    

        error_reporting(E_ALL);
        ini_set('display_errors', TRUE);
        ini_set('display_startup_errors', TRUE);
        date_default_timezone_set('Asia/Seoul');

        if (PHP_SAPI == 'cli')
            die('This example should only be run from a Web Browser');

        set_time_limit(120); 
        ini_set("memory_limit", "256M");

        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();

        // Set document properties
        $objPHPExcel->getProperties()->setCreator("Maarten Balliauw")
                                    ->setLastModifiedBy("Maarten Balliauw")
                                    ->setTitle("Office 2007 XLSX Test Document")
                                    ->setSubject("Office 2007 XLSX Test Document")
                                    ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
                                    ->setKeywords("office 2007 openxml php")
                                    ->setCategory("Test result file");


        // Add some data
        $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A1', '상태')
                    ->setCellValue('B1', '신청서번호')
                    ->setCellValue('C1', '결제번호')
                    ->setCellValue('D1', '주문자')
                    ->setCellValue('E1', '아이디')
                    ->setCellValue('F1', '회원유형')
                    ->setCellValue('G1', '결제카드사')
                    ->setCellValue('H1', '거래금액')
                    ->setCellValue('I1', '거래날짜');
        $i = 2;
        foreach ($list as $row){

            $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A'.$i, $row['status'])
                        ->setCellValue('B'.$i, $row['apply_code'])
                        ->setCellValue('C'.$i, $row['pg_orderno'])
                        ->setCellValue('D'.$i, $row['buyer_name']."(".$row['buyer_phone'].")")
                        ->setCellValue('E'.$i, $row['buyer_email'])
                        ->setCellValue('F'.$i, $row['user_type'])
                        ->setCellValue('G'.$i, $row['pg'])
                        ->setCellValue('H'.$i, $row['price'])
                        ->setCellValue('I'.$i, $row['payed_at']);
            $i++;
        }
                                
        // Rename worksheet
        $objPHPExcel->getActiveSheet()->setTitle('payment_list');


        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);


        // Redirect output to a client’s web browser (Excel2007)
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="user_list.xlsx"');
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');

        // If you're serving to IE over SSL, then the following may be needed
        header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
        header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header ('Pragma: public'); // HTTP/1.0

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;

        
    }
    

    

    

    


}
