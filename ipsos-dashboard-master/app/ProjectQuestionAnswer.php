<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProjectQuestionAnswer extends Model
{
    protected $table = 'project_question_answers';
    protected $fillable = [
        'project_id', 'question_id', 'code', 'answer'
    ];

    public function project()
    {
        return $this->belongsTo(Project::class, 'id', 'project_id');
    }
    
    public function projectQuestion()
    {
        return $this->belongsTo(ProjectQuestion::class, 'id', 'question_id');
    }
}
