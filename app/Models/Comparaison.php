<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comparaison extends Model
{
    

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = [
        'user_id',
        'goods_id_1',
        'goods_id_2',
        'goods_id_3',
        'goods_id_4',
        'goods_id_5',
        'created_at',
    ];
}
