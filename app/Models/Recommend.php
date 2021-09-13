<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Recommend extends Model
{
    

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = [
        'goods_id',
        'order_no',
        'image_src',
        'created_at',
    ];
}
