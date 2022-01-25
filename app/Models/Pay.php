<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pay extends Model
{
    

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = [
        'reservation_id',
        'user_id',
        'price',
        'amount',
        'state',
        'paid_at',
        'created_at',
        'updated_at',
    ];
}
