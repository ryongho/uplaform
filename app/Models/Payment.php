<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = [
        'reservation_id',
        'imp_uid',
        'merchant_uid',
        'order_name',
        'user_id',
        'price',
        'device',
        'pg',
        'pg_orderno',
        'detail',
        'payed_at',
        'status',
        'buyer_name',
        'buyer_email',
        'buyer_phone',
        'buyer_address',
        'created_at',
    ];
}
