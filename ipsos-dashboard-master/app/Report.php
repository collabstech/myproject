<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\User;

class Report extends Model
{
    use SoftDeletes;
    protected $table = 'reports';

    protected $fillable = [
        'uuid', 'name', 'type', 'row', 'column', 'data', 'operation', 
        'project_id', 'trendline', 'summary', 'showvalues',
        'row_combo2', 'data_combo2', 'operation_combo2', 
    ];

    const TYPE_TABLE = 1;
    const TYPE_BAR = 2;
    const TYPE_PIE = 3;
    const TYPE_LINE = 4;
    const TYPE_BAR_LINE = 5;

    const OPERATION_SUM = 1;
    const OPERATION_COUNT = 2;
    const OPERATION_AVG = 3;

    const SHOW_NUMBER = 1;
    const SHOW_PERCENTAGE = 2;
    
    const SORT_BY_DATE = 'Q3';
    const Q_NOT_INTEGER = 'Q1';

    public static function typeLabel()
    {
        return [
            self::TYPE_TABLE => 'Table',
            self::TYPE_BAR => 'Bar Chart',
            self::TYPE_PIE => 'Pie Chart',
            self::TYPE_LINE => 'Line Chart',
            self::TYPE_BAR_LINE => 'Bar Line Chart',
        ];
    }

    public static function operationLabel()
    {
        return [
            self::OPERATION_SUM => 'Sum',
            self::OPERATION_AVG => 'Avg',
            self::OPERATION_COUNT => 'Count',
        ];
    }

    public static function typeVisible()
    {
        return [
            self::TYPE_TABLE => 'visibleTypeTable',
            self::TYPE_BAR => 'visibleTypeBar',
            self::TYPE_PIE => 'visibleTypePie',
            self::TYPE_LINE => 'visibleTypeLine',
            self::TYPE_BAR_LINE => 'visibleTypeBarLine',
        ];
    }

    public function reportFilter()
    {
        return $this->hasMany(ReportFilter::class);
    }

    public function reportFilterSummary()
    {
        return $this->hasMany(ReportFilterSummary::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function rowQuestion()
    {
        return $this->belongsTo(ProjectQuestion::class, 'row', 'id');
    }

    public function rowQuestion2()
    {
        return $this->belongsTo(ProjectQuestion::class, 'row_combo2', 'id');
    }

    public function columnQuestion()
    {
        return $this->belongsTo(ProjectQuestion::class, 'column', 'id');
    }

    public function dataQuestion()
    {
        return $this->belongsTo(ProjectQuestion::class, 'data', 'id');
    }

    public function dataQuestion2()
    {
        return $this->belongsTo(ProjectQuestion::class, 'data_combo2', 'id');
    }

    public function rowValues()
    {
        return $this->hasMany(ProjectResultValue::class, 'question_id', 'row');
    }

    public function rowValues2()
    {
        return $this->hasMany(ProjectResultValue::class, 'question_id', 'row_combo2');
    }

    public function columnValues()
    {
        return $this->hasMany(ProjectResultValue::class, 'question_id', 'column');
    }

    public function dataValues()
    {
        return $this->hasMany(ProjectResultValue::class, 'question_id', 'data');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getCreatedByNameAttribute($value)
    {
        if (!$this->created_by) {
            return null;
        }
        if ($user = User::where('id', $this->created_by)->first()){
            return $user->name;
        }
    }

    public function getUpdatedByNameAttribute($value)
    {
        if (!$this->updated_by) {
            return null;
        }
        if ($user = User::where('id', $this->updated_by)->first()){
            return $user->name;
        }
    }

    public function reportSummary()
    {
        return $this->hasMany(ReportSummary::class);
    }
}
