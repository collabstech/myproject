<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProjectResultValue extends Model
{
    protected $table = 'project_result_values';
    protected $fillable = [
        'row', 'project_id', 'result_id', 'question_id', 'answer_id', 'answer_column', 'values',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function result()
    {
        return $this->belongsTo(ProjectResult::class, 'id', 'sample_id');
    }
    
    public function question()
    {
        return $this->belongsTo(ProjectQuestion::class);
    }
}
