<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Push extends Model
{
    

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = [
        'user_id',
        'type',
        'target_user',
        'target_id',
        'content',
        'send_date',
        'state',
        'created_at',
        'updated_at',
    ];


    public static function send_push($push){
        
        $fields = array( 'registration_ids' => $push->token, 'notification' => array( 'title'=>$push->title, 'body'=>$push->body, ), 'data' => array('url'=>''), 'priority'=>'high' ); 
        $headers = array( 'Authorization:key ='.'AAAAs5EgmJE:APA91bGsdzPjAquYUviYc92g2BFBODH9K1LkPWPmfMyReppChlg7XvVAdzr_fOIIGrSNYgtjyXJg4bJYHJJhiWUqDSBeZMbJhJIdd6ZELU-_Pz2YGFsBrhrSSXz2INbKPAbLYUdb9UnB', 'Content-Type: application/json' ); //firebase에서 키값으로 호출 형식 
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        $result = curl_exec($ch);
        if ($result === FALSE) { 
            die('Curl failed: ' . curl_error($ch)); 
        } 
        curl_close($ch); 
        echo $result; 
        exit;
        $json = json_decode($result,true); 
        return $json['success'];
    }
    

}
