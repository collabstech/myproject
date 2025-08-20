<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    protected $fillable = [
        'name'
    ];

    public function retailAchievement() 
    {
        return $this->belongsToMany('RetailAchievement', 'brand_retail_achievement');
    }
}
