<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Interviewer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'project_id', 
        'main_dealer_code', 
        'main_dealer_name', 
        'interviewer_id',
        'achievement', 
        'achievement_percent'
    ];
}
