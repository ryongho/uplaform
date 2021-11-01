<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Goods;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\SmsLog;
use App\Models\Reservation;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class SMSController extends Controller
{
    public function log_regist($result){    
        
        SmsLog::insert([
            'phone'=> $result->phone,
            'status'=> $result->status ,
            'content'=> $result->content ,
            'fail_reason'=> $result->fail_reason ,
            'send_date'=> $result->send_date ,
            'created_at'=> Carbon::now(),
        ]);
    }

    public function send($sms)
    {    
        $_api_url = 'https://message.ppurio.com/api/send_utf8_json.php';     // UTF-8 인코딩과 JSON 응답용 호출 페이지
        
        $_param['userid'] = 'rooming';           // [필수] 뿌리오 아이디
        $_param['callback'] = '01062328507';    // [필수] 발신번호 - 숫자만
        $_param['phone'] = $sms->phone;       // [필수] 수신번호 - 여러명일 경우 |로 구분 '010********|010********|010********'
        $_param['msg'] = $sms->content;   // [필수] 문자내용 - 이름(names)값이 있다면 [*이름*]가 치환되서 발송됨
        //$_param['names'] = '홍길동';            // [선택] 이름 - 여러명일 경우 |로 구분 '홍길동|이순신|김철수'
        //$_param['appdate'] = '20190502093000';  // [선택] 예약발송 (현재시간 기준 10분이후 예약가능)
        $_param['subject'] = '테스트';          // [선택] 제목 (30byte)
        //$_param['file1'] = '@이미지파일경로;type=image/jpg'; // [선택] 포토발송 (jpg, jpeg만 지원  300 K  이하)

        $_curl = curl_init();
        curl_setopt($_curl,CURLOPT_URL,$_api_url);
        curl_setopt($_curl,CURLOPT_POST,true);
        curl_setopt($_curl,CURLOPT_SSL_VERIFYPEER,false);
        curl_setopt($_curl,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($_curl,CURLOPT_POSTFIELDS,$_param);
        $_result = curl_exec($_curl);
        curl_close($_curl);

        $_result = json_decode($_result);
        
        if($_result->result == "ok"){
            $sms->status = "S";
            $sms->send_date = Carbon::now();
            $sms->fail_reason = $_result->result;
            $sms->msgid = $_result->msgid;
            $sms->ok_cnt = $_result->ok_cnt;
            $sms->type = $_result->type;

        }else{
            $sms->status = "F";
            $sms->send_date = Carbon::now();
            $sms->fail_reason = $_result->result;
        }

        $this->log_regist($sms);
    
    }



    



}
