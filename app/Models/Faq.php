<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = [
        'type',
        'title',
        'content',
        'writer',
        'updated_at',
        'created_at',
    ];
}
