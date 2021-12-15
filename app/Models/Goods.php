<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Goods extends Model
{
    

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = [
        'hotel_id',
        'room_id',
        'goods_name',
        'content',
        'start_date',
        'end_date',
        'nights',
        'options',
        'type',
        'price',
        'sale_price',
        'amount',
        'min_nights',
        'max_nights',
        'breakfast',
        'parking',
        'created_at',
    ];
}
