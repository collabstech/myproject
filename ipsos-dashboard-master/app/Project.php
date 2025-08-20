<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use SoftDeletes;
    
    const STANDARD_TYPE = 0;
    const CSI_TYPE = 1;
    const CSL_TYPE = 2;
    const WHEEL_TYPE = 3;
    const BLACKHOLE_TYPE = 4;
    const CQI_TYPE = 5;

    const TYPE_NAME = array(
        \App\Project::STANDARD_TYPE => 'Standard',
        \App\Project::CSI_TYPE => 'CSI',
        \App\Project::CSL_TYPE => 'CSL',
        \App\Project::WHEEL_TYPE => 'Wheel Census',
        \App\Project::BLACKHOLE_TYPE => 'Blackhole',
        \App\Project::CQI_TYPE => 'CQI'
    );

    protected $table = 'projects';

    protected $fillable = [
        'uuid', 'company_id', 'code', 'name', 'description', 'objective', 
        'start_date', 'finish_date', 'respondent', 'type',
        'coverage', 'methodology', 'timeline', 'chart_titles', 
        'store_stat_titles', 'store_stat_values',
        'created_at', 'updated_at',
    ];

    protected $dates = [
        'start_date', 'finish_date',
    ];

    public function projectQuestion()
    {
        return $this->hasMany(ProjectQuestion::class);
    }

    public function projectQuestionAnswer()
    {
        return $this->hasMany(ProjectQuestionAnswer::class);
    }

    public function report()
    {
        return $this->hasMany(Report::class);
    }

    public function projectResult()
    {
        return $this->hasMany(ProjectResult::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function userProject()
    {
        return $this->hasMany(UserProject::class);
    }

    public function companyProject()
    {
        return $this->hasMany(CompanyProject::class);
    }
}
