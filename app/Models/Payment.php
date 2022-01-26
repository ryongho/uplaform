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
        'user_id',
        'imp_uid',
        'pay_method',
        'merchant_uid',
        'name',
        'paid_amount',
        'currency',
        'pg_provider',
        'pg_type',
        'pg_tid',
        'apply_num',
        'buyer_name',
        'buyer_phone',
        'buyer_email',
        'buyer_addr',
        'custom_data',
        'paid_at',
        'status',
        'receipt_url',
        'cpid',
        'data',
        'card_name',
        'bank_name',
        'card_quota',
        'card_number',
        'created_at',
    ];
}
