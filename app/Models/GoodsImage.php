<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoodsImage extends Model
{
    

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = [
        'goods_id',
        'file_name',
        'order_no',
        'created_at'
    ];
}
