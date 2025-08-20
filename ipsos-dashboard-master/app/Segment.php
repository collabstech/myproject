<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Segment extends Model
{
    protected $fillable = [
        'name'
    ];

    public function retailAchievement() 
    {
        return $this->belongsToMany('RetailAchievement', 'segment_retail_achievement');
    }
}
