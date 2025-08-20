<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProjectResult extends Model
{
    protected $table = 'project_results';
    protected $fillable = [
        'uuid', 'project_id', 'result_date', 'result_code',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function projectResultValue()
    {
        return $this->hasMany(ProjectResultValue::class, 'result_id', 'id');
    }
}
