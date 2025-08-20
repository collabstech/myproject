<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ReportSummary extends Model
{
    protected $table = 'report_summary';
    protected $fillable = [
        'report_id', 'question_id',
    ];

    public function report()
    {
        return $this->belongsTo(Report::class);
    }

    public function question()
    {
        return $this->belongsTo(ProjectQuestion::class, 'question_id', 'id');
    }
}
