<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Carbon;
//use App\Models\PaymentLog;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class Payment extends Model
{
    public static function log_regist($result){    
        
        SmsLog::insert([
            'phone'=> $result->phone,
            'status'=> $result->status ,
            'content'=> $result->content ,
            'fail_reason'=> $result->fail_reason ,
            'send_date'=> $result->send_date ,
            'created_at'=> Carbon::now(),
        ]);
    }

    public static function payment($info){
        $ctf = Payment::certify(); // 페이플 파트너 인증

        //dd($ctf);

        /* 
        * 링크생성 요청
        * TEST : https://democpay.payple.kr
        * REAL : https://cpay.payple.kr
        */
        /*POST 파트너 인증 후 리턴받은 PCD_PAY_URL HTTP/1.1
        Host: 파트너 인증 후 리턴받은 PCD_PAY_HOST
        Content-Type: application/json
        Cache-Control: no-cache
        {
            "PCD_CST_ID": "파트너 인증 후 리턴받은 cst_id",
            "PCD_CUST_KEY": "파트너 인증 후 리턴받은 custKey",
            "PCD_AUTH_KEY": "파트너 인증 후 리턴받은 AuthKey",  
            "PCD_PAY_WORK": "LINKREG",
            "PCD_PAY_TYPE": "card",
            "PCD_PAY_GOODS": "상품1",
            "PCD_PAY_TOTAL": "100",
            "PCD_LINK_EXPIREDATE": "20200806"
        }
        JSON
        */

        header("Expires: Mon 26 Jul 1997 05:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d, M Y H:i:s") . " GMT");
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Cache-Control: post-check=0; pre-check=0", false);
        header("Pragma: no-cache");
        header("Content-type: application/json; charset=utf-8");

        $CURLOPT_HTTPHEADER = array(
            "referer: http://localhost:80" // 필수
        );
        
        $post_data = array (
            "PCD_CST_ID" => $ctf->cst_id,
            "PCD_CUST_KEY" => $ctf->custKey,
            "PCD_AUTH_KEY" => $ctf->AuthKey, 
            "PCD_PAY_WORK" => "LINKREG",
            "PCD_PAY_TYPE" => "card",
            "PCD_PAY_GOODS" => $info->goods_name."_".$info->name."_".$info->reservation_no,
            "PCD_PAY_TOTAL" => $info->price,
            "PCD_LINK_EXPIREDATE" => $info->expire,
        );
        
        $ch = curl_init($ctf->return_url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $CURLOPT_HTTPHEADER);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));

        ob_start();
        $payRes = curl_exec($ch);
        $payBuffer = ob_get_contents();
        ob_end_clean();

        

        $payResult = json_decode($payBuffer);
        dd($payResult);
        
        
        $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close ($ch);
        if($status_code == 200) {
            dd(json_decode($buffer));
        }else{
            dd($status_code);
        }

        


    }

    public static function certify(){
        /* 
        * TEST : https://democpay.payple.kr/php/auth.php
        * REAL : https://cpay.payple.kr/php/auth.php 
        */
        header("Expires: Mon 26 Jul 1997 05:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d, M Y H:i:s") . " GMT");
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Cache-Control: post-check=0; pre-check=0", false);
        header("Pragma: no-cache");
        header("Content-type: application/json; charset=utf-8");

        /*  ※ Referer 설정 필독
        *  REAL : referer에는 가맹점 도메인으로 등록된 도메인을 넣어주셔야합니다. 
        *         다른 도메인을 넣으시면 [AUTH0004] 응답이 발생합니다.
        */
        $CURLOPT_HTTPHEADER = array(
            "referer: http://localhost:80" // 필수
        );

        // 발급받은 비밀키. 유출에 주의하시기 바랍니다.
        $post_data = array (
            "cst_id" => "test",
            "custKey" => "abcd1234567890",
            "PCD_PAY_TYPE" => "LINKREG"
        );

        $ch = curl_init('https://democpay.payple.kr/php/auth.php');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $CURLOPT_HTTPHEADER);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));

        ob_start();
        $response = curl_exec ($ch);
        $buffer = ob_get_contents();
        ob_end_clean();

        $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close ($ch);
        if($status_code == 200) {
            return json_decode($buffer);
        }
    }

    
}
