<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProgressData extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'project_id', 
        'brand',
        'main_dealer_code', 
        'main_dealer_name', 
        'district', 
        'dealer_code',
        'dealer_name',
        'h1_premium_target',
        'h1_premium_achievement',
        'h2_premium_target',
        'h2_premium_achievement',
        'h3_premium_target',
        'h3_premium_achievement',
        'total_target_premium',
        'total_achievement_premium', 
        'h1_regular_target',
        'h1_regular_achievement',
        'h2_regular_target',
        'h2_regular_achievement',
        'h3_regular_target',
        'h3_regular_achievement',
        'total_target_regular',
        'total_achievement_regular',
        'h1_total',
        'h2_total',
        'h3_total',
        'total'
    ];
}
