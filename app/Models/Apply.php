<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Apply extends Model
{
    

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = [
        'reservation_id',
        'user_id',
        'status',
        'created_at',
        'updated_at',
    ];
}
