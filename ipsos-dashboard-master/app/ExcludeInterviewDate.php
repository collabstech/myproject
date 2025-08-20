<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ExcludeInterviewDate extends Model
{
    protected $table = 'exclude_interview_dates';

    protected $fillable = [
        'date', 'project_id'
    ];
}
