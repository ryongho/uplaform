<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = [
        'hotel_id',
        'name',
        'size',
        'bed',
        'amount',
        'peoples',
        'options',
        'price',
        'created_at',
    ];
}
