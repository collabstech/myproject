<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProgressWeek extends Model
{
    protected $table = 'progress_weeks';

    protected $fillable = [
        'project_id', 
        'main_dealer_code', 
        'main_dealer_name', 
        'week',
        'date', 
        'target',
        'achievement',
        'achievement_percent'
    ];
}
