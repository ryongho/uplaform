<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsLog extends Model
{
    

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = [
        'phone',
        'content',
        'send_date',
        'status',
        'fail_reason',
        'ok_cnt',
        'msgid',
        'type',
        'created_at',
    ];
}
