<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProjectQuestion extends Model
{
    protected $table = 'project_questions';
    protected $fillable = [
        'project_id', 'code', 'question', 'question_alias',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function projectQuestionAnswer()
    {
        return $this->hasMany(ProjectQuestionAnswer::class, 'question_id');
    }

    public function getAliasAttribute($value)
    {
        return $this->question_alias ? $this->question_alias : $this->question;
    }
}
