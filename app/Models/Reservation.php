<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = [
        'user_id',
        'reservation_no',
        'goods_id',
        'hotel_id',
        'room_id',
        'start_date',
        'end_date',
        'nights',
        'price',
        'peoples',
        'request',
        'status',
        'created_at',
        'updated_at',
    ];
}
