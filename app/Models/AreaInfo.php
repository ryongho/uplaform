<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AreaInfo extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    
    protected $fillable = [
        'user_id',
        'interest_service',
        'house_type',
        'peoples',
        'house_size',
        'area_size',
        'address',
        'tel',
        'shop_type',
        'shop_size',
        'kitchen_size',
        'refrigerator',
        'refrigerator_size',
        'shop_name',
        'ceo_name',
        'updated_at',
        'created_at'
    ];


    

}
