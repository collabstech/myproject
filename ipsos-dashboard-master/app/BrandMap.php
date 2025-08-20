<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BrandMap extends Model
{
    protected $table = 'brand_map';

    protected $fillable = [
        'map_id', 'brand', 'sales'
    ];
}
