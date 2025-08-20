<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

class CompanyProject extends Pivot
{
    protected $table = 'company_projects';
    protected $fillable = [
        'company_id', 'project_id',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
    
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

}
