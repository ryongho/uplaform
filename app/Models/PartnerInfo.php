<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartnerInfo extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    
    protected $fillable = [
        'user_id',
        'service_type',
        'partner_type',
        'confirm_history',
        'activity_distance',
        'license_img',
        'reg_img',
        'biz_type',
        'reg_no',
        'biz_name',
        'address',
        'address2',
        'ceo_name',
        'tel',
        'position',
        'job',
        'updated_at',
        'created_at'
    ];


    

}
