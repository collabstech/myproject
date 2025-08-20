<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RetailAchievement extends Model
{
    protected $fillable = [
        'project_id', 'respondent_id', 'province', 'kabupaten', 'kecamatan', 'kelurahan', 'segment_id'
    ];

    public function segments() 
    {
        return $this->belongsToMany('\App\Segment', 'segment_retail_achievement');
    }

    public function brands() 
    {
        return $this->belongsToMany('\App\Brand', 'brand_retail_achievement');
    }
}
