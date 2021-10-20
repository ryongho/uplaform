<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Push extends Model
{
    

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = [
        'user_id',
        'type',
        'target_user',
        'content',
        'send_date',
        'created_at',
        'updated_at',
    ];
}
