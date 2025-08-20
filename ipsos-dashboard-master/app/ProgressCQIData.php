<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProgressCQIData extends Model
{
    protected $table = 'progress_cqi_datas';

    protected $fillable = [
        'project_id', 
        'main_dealer', 
        'district', 
        'type',
        'model',
        'target',
        'actual'
    ];
}
