<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RetailProgress extends Model
{
    protected $fillable = [
        'project_id', 'sample_id', 'province', 'kabupaten', 'kecamatan', 'kelurahan',
        'weeks', 'number_of_interview', 'status'
    ];
}
