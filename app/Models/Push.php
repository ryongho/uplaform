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
        'target_id',
        'content',
        'send_date',
        'state',
        'created_at',
        'updated_at',
    ];
}
