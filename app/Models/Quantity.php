<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quantity extends Model
{
    

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = [
        'goods_id',
        'date',
        'qty',
        'created_at',
        'updated_at',
    ];
}
