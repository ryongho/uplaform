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
        'reservation_no',
        'reservation_type',
        'services',
        'service_detail',
        'service_date',
        'service_time',
        'service_addr',
        'start_date',
        'phone',
        'memo',
        'price',
        'learn_day',
        'status',
        'created_at',
        'updated_at',
    ];
}
