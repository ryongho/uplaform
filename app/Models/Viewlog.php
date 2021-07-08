<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Viewlog extends Model
{
    

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = [
        'user_id',
        'goods_id',
        'created_at',
    ];
}
