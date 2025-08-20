<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Map extends Model
{
    protected $fillable = [
        'project_id', 'respondent_id', 'province', 'kabupaten', 'kecamatan', 'kelurahan', 'segment', 'lat', 'lon', 'name', 'address', 'photo'
    ];
}
