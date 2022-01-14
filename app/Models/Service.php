<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = [
        'service_type',
        'service_sub_type',
        'service_part',
        'service_name',
        'price',
        'memo',
        'visit_count',
        'time',
        'send_date',
        'created_at',
        'updated_at',
    ];
}
