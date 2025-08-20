<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ReportFilterSummary extends Model
{
    protected $table = 'report_filters_summary';
    protected $fillable = [
        'report_id', 'project_id', 'question_id', 'default_answer', 'user_id', 
    ];

    public function report()
    {
        return $this->belongsTo(Report::class);
    }

    public function question()
    {
        return $this->belongsTo(ProjectQuestion::class, 'question_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
