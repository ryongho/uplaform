<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Payment;
use App\Models\AreaInfo;
use App\Models\Reservation;
use App\Models\Apply;
use App\Models\Pay;
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

        $list = array();
        $i = 0;

        foreach($rows as $row){

            $list[$i]['id'] = $row->id;
            $list[$i]['email'] = $row->email;
            $list[$i]['phone'] = $row->phone;
            $list[$i]['name'] = $row->name;
            $list[$i]['gender'] = $row->gender;
            $list[$i]['add_info'] = $row->add_info;
            $list[$i]['reservation_cnt'] = $row->reservation_cnt;
            $list[$i]['payment_cnt'] = $row->payment_cnt;
            $list[$i]['created_at'] = $row->created_at->format('Y-m-d H:i:s');
            $list[$i]['last_login'] = $row->last_login;
            $list[$i]['leave'] = $row->leave;



            if($row->sns_key != ""){
                $sns_keys = explode('_',$row->sns_key);
                $list[$i]['user_type'] = $sns_keys[0];
                $list[$i]['email'] = $row->sns_key;
            }else{
                $list[$i]['user_type'] = "유플랫폼";
            }
            //add_info
            $area_cnt = AreaInfo::where('user_id',$row->id)->count();
            if($area_cnt){
                $list[$i]['add_info'] = "Y";
            }else{
                $list[$i]['add_info'] = "N";
            }

            if($row->leave == "Y"){ 
                $list[$i]['status'] = "탈퇴";
            }else{
                $list[$i]['status'] = "정상";
            }

            //reservation_cnt
            $list[$i]['reservation_cnt'] = Reservation::where('user_id',$row->id)->count();
            //payment_cnt
            $list[$i]['payment_cnt'] = Payment::where('user_id',$row->id)->count();
            
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

        foreach ($list as $row){

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
                        ->setCellValue('L'.$i, $row['status']);
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
    
    public function partner_list(Request $request){
        ob_start();
        
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $search_type = $request->search_type;
        $search_keyword = $request->search_keyword;

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
                )
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
                ->orderBy('users.id', 'desc')->get();

        $list = array();
        $i = 0;

        foreach($rows as $row){

            $list[$i]['user_id'] = $row->user_id;
            $list[$i]['partner_id'] = $row->partner_id;
            //$list[$i]['approval'] = $row->approval;
            $list[$i]['approved_at'] = $row->approved_at;
            //$list[$i]['partner_type'] = $row->partner_type;
            $list[$i]['email'] = $row->email;
            $list[$i]['sns_key'] = $row->sns_key;
            $list[$i]['phone'] = $row->phone;
            $list[$i]['name'] = $row->name;
            $list[$i]['gender'] = $row->gender;
            $list[$i]['created_at'] = $row->created_at->format('Y-m-d H:i:s');
            $list[$i]['last_login'] = $row->last_login;
            $list[$i]['leave'] = $row->leave;

            if($row->sns_key != ""){ // sns로그인인 경우
                $sns_keys = explode('_',$row->sns_key );
                $list[$i]['user_type'] = $sns_keys[0];
                $list[$i]['email'] = $row->sns_key ;
            }else{
                $list[$i]['user_type'] = "유플랫폼";
            }

            //matching_cnt
            $list[$i]['matching_cnt'] = Apply::where('user_id',$row->user_id)->where('status','S')->count();
            
            //payment_cnt
            $list[$i]['pay_cnt'] = Pay::where('user_id',$row->user_id)->count();

            if($row->leave == "Y"){ 
                $list[$i]['status'] = "탈퇴";
            }else{
                $list[$i]['status'] = "정상";
            }

            if($row->partner_type == "CS"){ 
                $list[$i]['partner_type'] = "공간정리";
            }elseif($row->partner_type == "CR"){
                $list[$i]['partner_type'] = "위생정리";
            }elseif($row->partner_type == "LC"){
                $list[$i]['partner_type'] = "정리교육";
            }else{
                $list[$i]['partner_type'] = "";
            }

            if($row->approval == "Y"){ 
                $list[$i]['approval'] = "승인완료";
            }elseif($row->approval == "N"){
                $list[$i]['approval'] = "승인대기";
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
                    ->setCellValue('A1', '번호')
                    ->setCellValue('B1', '회원상태')
                    ->setCellValue('C1', '승인일시')
                    ->setCellValue('D1', '회원번호')
                    ->setCellValue('E1', '전문가유형')
                    ->setCellValue('F1', '이메일(아이디)')
                    ->setCellValue('G1', '휴대폰번호')
                    ->setCellValue('H1', '이름')
                    ->setCellValue('I1', '회원유형')
                    ->setCellValue('J1', '성별')
                    ->setCellValue('K1', '매칭')
                    ->setCellValue('L1', '정산')
                    ->setCellValue('M1', '가입일')
                    ->setCellValue('N1', '최종로그인')
                    ->setCellValue('O1', '상태');

        $i = 2;

        foreach ($list as $row){

            $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A'.$i, ($i-1))
                        ->setCellValue('B'.$i, $row['approval'])
                        ->setCellValue('C'.$i, $row['approved_at'])
                        ->setCellValue('D'.$i, $row['user_id'])
                        ->setCellValue('E'.$i, $row['partner_type'])
                        ->setCellValue('F'.$i, $row['email'])
                        ->setCellValue('G'.$i, $row['phone'])
                        ->setCellValue('H'.$i, $row['name'])
                        ->setCellValue('I'.$i, $row['user_type'])
                        ->setCellValue('J'.$i, $row['gender'])
                        ->setCellValue('K'.$i, $row['matching_cnt'])
                        ->setCellValue('L'.$i, $row['pay_cnt'])
                        ->setCellValue('M'.$i, $row['created_at'])
                        ->setCellValue('N'.$i, $row['last_login'])
                        ->setCellValue('O'.$i, $row['status']);
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
    

    

    

    


}
