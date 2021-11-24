<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = [
        'user_id',
        'device_id',
        'model',
        'app_version',
        'os',
        'created_at',
        'updated_at',
    ];
}
