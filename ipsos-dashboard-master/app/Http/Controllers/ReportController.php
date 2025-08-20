<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Project;
use App\ProjectQuestionAnswer;
use App\ProjectResultValue;
use App\Report;
use App\User;

use App\Http\Requests\GenerateReportRequest;
use App\Http\Requests\ReportTrendlineRequest;

use App\Http\Helpers\ExportToPpt;

use Carbon\Carbon;
use DataTables;
use Excel;
use PDF;
use Auth;
use Carbon\CarbonPeriod;

class ReportController extends Controller
{
    private $projectModel;
    private $reportModel;
    private $answerModel;
    private $valueModel;

    public function __construct(
        Project $project,
        Report $report,
        ProjectQuestionAnswer $answer,
        ProjectResultValue $value
        ) {
        $this->reportModel = $report;
        $this->projectModel = $project;
        $this->answerModel = $answer;
        $this->valueModel = $value;
    }

    public function listData($projectId, Request $request)
    {
        $project = $this->projectModel->where('uuid', $projectId)->first();
        \DB::statement(\DB::raw('set @rownum='.$request->start));
        $model = $this->reportModel->where('project_id', $project->id)->select([
            \DB::raw('@rownum  := @rownum  + 1 AS rownum'),
            'reports.uuid', 'reports.name', 'type', 'user_id',
            'reports.created_by', 'reports.updated_by',
            'reports.created_at', 'reports.updated_at',
        ])
        ->with('user')
        ;

        $datatables = DataTables::of($model);

        $datatables->editColumn('type', function ($model) {
            return Report::typeLabel()[$model->type];
        });

        $datatables->editColumn('created_by_name', function ($model) {
            return $model->created_by_name;
        });

        $datatables->editColumn('updated_by_name', function ($model) {
            return $model->updated_by_name;
        });
        
        $datatables->editColumn('action', function ($model) use ($project) {
            $html = '';
            $html .= '<a href="'.route('report.generate', ['project_id' => $project->uuid, 'report_id' => $model->uuid]).'" class="btn btn-primary"><i class="fa fa-info-circle"></i> View </a>';
            if ($model->user) {
                if (Auth::user()->id == $model->user->id || $model->user->role != User::ROLE_ADMIN) {
                    $html .= '&nbsp;<a href="javascript:;" class="btn btn-danger btn-delete" projectid="'.$project->uuid.'" reportid="'.$model->uuid.'"><i class="fa fa-trash"></i> Delete</a>';
                }
            }

            return $html;
        });

        return $datatables->make(true);
    }

    public function generate($projectId, $reportId = '', Request $request)
    {
        $project = $this->projectModel
                        ->where('uuid', $projectId)
                        ->with(['projectResult', 'projectQuestion'])
                        ->first();
        $reportType = $this->generateTypeList($project);
        $reportOperation = Report::operationLabel();
        $report = $this->reportModel->where('uuid', $reportId)->first();
        $reportFilter = [];
        $filterAnswer = [];
        if (isset($report->reportFilter)) {
            $reportFilter = $report->reportFilter;
            foreach ($reportFilter as $key => $value) {
                $filterAnswer[$value->question_id] = explode('|', $value->default_answer);
            }
        }

        $reportFilterSummary = [];
        $filterAnswerSummary = [];
        if (isset($report->reportFilterSummary)) {
            $reportFilterSummary = $report->reportFilterSummary;
            foreach ($reportFilterSummary as $key => $value) {
                $filterAnswerSummary[$value->question_id] = explode('|', $value->default_answer);
            }
        }

        $summaryField = [];
        if (isset($report->reportSummary)) {
            $reportSummary = $report->reportSummary;
            foreach ($reportSummary as $key => $value) {
                $summaryField[$value->question_id] = $value->question_id;
            }
        }


        $reportAdmin = $this->reportModel
        ->where('project_id', $project->id)
        ->get()
        ;
        $questionList = $project->projectQuestion;
        $questionArray = [];
        foreach ($questionList as $key => $value) {
            $questionArray[$value->id] = $value;
        }

        $data = [];
        $data['project'] = $project;
        $data['report'] = $report;
        $data['reportAdmin'] = $reportAdmin;
        $data['reportType'] = $reportType;
        $data['reportOperation'] = $reportOperation;
        $data['reportFilter'] = $reportFilter;
        $data['reportFilterSummary'] = $reportFilterSummary;
        $data['filterAnswer'] = $filterAnswer;
        $data['filterAnswerSummary'] = $filterAnswerSummary;
        $data['questionList'] = $questionList;
        $data['questionArray'] = $questionArray;
        $data['questionFilteredList'] = $this->generateQuestionList($questionList);
        $data['summaryField'] = $summaryField;
        
        if ($report) {
            $generatedValue = $this->generateValueReport($project, $report, $request);
            if ($generatedValue) {
                $data = array_merge($data, $generatedValue);
            }
        }

        return view('report.generate', $data);
    }

    private function generateTypeList($project)
    {
        $user = Auth::user();
        $reportType = Report::typeLabel();
        $type = [];
        if ($user->role != User::ROLE_ADMIN) {
            foreach (Report::typeVisible() as $key => $value) {
                if ($project->{$value} == 1) {
                    $type[$key] = $reportType[$key];
                }
            }
        } else {
            $type = $reportType;
        }

        return $type;
    }

    private function generateQuestionList($questionList)
    {
        $user = Auth::user();
        $questionFilteredList = [];
        $questionFilteredList = [
            'row' => $user->role == User::ROLE_ADMIN ? $questionList : $questionList->where('visibleSide', 1),
            'column' => $user->role == User::ROLE_ADMIN ? $questionList : $questionList->where('visibleTop', 1),
            'value' => $user->role == User::ROLE_ADMIN ? $questionList : $questionList->where('visibleValue', 1),
            'filter' => $user->role == User::ROLE_ADMIN ? $questionList : $questionList->where('visibleFilter', 1),
            'summary' => $user->role == User::ROLE_ADMIN ? $questionList : $questionList->where('visibleSummary', 1),
        ];

        return $questionFilteredList;
    }

    private function generateValueReport($project, $report, Request $request)
    {
        $keyDate = '';
        $projectResult = $project->projectResult()->orderBy('id', 'desc')->first();
        if (!$projectResult || !$projectResult->has('projectResultValue')) {
            return [];
        }
        $questionList = $project->projectQuestion()->get();
        $questionAlias = [];
        foreach ($questionList as $key => $value) {
            if ($value->code == 'Q3') {
                $keyDate = $value->alias;
            }

            $questionAlias[$value->id] = $value->alias;
        }

        $resultItem = $projectResult->projectResultValue()
                                    ->select(['id', 'row', 'question_id', 'answer_column', 'values'])
                                    ->get();

        $rowQuestion = $report->rowQuestion->alias;
        $columnQuestion = $report->columnQuestion->alias;
        $dataQuestion = $report->dataQuestion->alias;
        
        $rowQuestion2 = $report->rowQuestion2 ? $report->rowQuestion2->alias : null;
        $dataQuestion2 = $report->dataQuestion2 ? $report->dataQuestion2->alias : null;

        $res = [];
        foreach ($resultItem as $key => $value) {
            $alias = $questionAlias[$value->question_id];
            $res[$value->row][$alias][$value->answer_column] = $value->values;
        }

        $result = [];
        if ($report->reportFilter()->count() > 0) {
            $result = collect($res);
            $this->filterResult($report, $result);
        } else {
            $result = $res;
        }

        $resultSummary = [];
        if ($report->reportFilterSummary()->count() > 0) {
            $resultSummary = collect($result);
            $this->filterResultSummary($report, $resultSummary);
        } else {
            $resultSummary = $result;
        }

        $generatedValue = [];
        $resultValue = [];
        $resultColumn = [];
        $resultRow = [];
        $resultRow2 = [];
        $resultTotal = [];
        $resultPercentOfColumn = [];
        $resultRowPercentOfColumn = [];
        $resultRowPercentOfColumn2 = [];

        // Side header values
        if ($report->rowValues) {
            $filterRow = $report->reportFilter()->where('question_id', $report->row)->whereNotNull('default_answer')->first();
            $rowValues = $report->rowValues()->where('result_id', $projectResult->id)->orderBy('answer_id');
            if (isset($filterRow)) {
                $filter = explode('|', $filterRow->default_answer);
                $rowValues = $rowValues->whereIn('values', $filter);
            }
            $generatedValue['row'] = [];
            foreach ($rowValues->get() as $key => $value) {
                $generatedValue['row'][$value->values] = $value->values;
            }
        }

        // Side 2 header values
        if ($report->rowValues2) {
            $filterRow = $report->reportFilter()->where('question_id', $report->row_combo2)->whereNotNull('default_answer')->first();
            $rowValues2 = $report->rowValues2()->where('result_id', $projectResult->id)->orderBy('answer_id');
            if (isset($filterRow)) {
                $filter = explode('|', $filterRow->default_answer);
                $rowValues2 = $rowValues2->whereIn('values', $filter);
            }

            $generatedValue['row2'] = [];
            foreach ($rowValues2->get() as $key => $value) {
                $generatedValue['row2'][$value->values] = $value->values;
            }

        }
        
        // Top header values
        if ($report->columnValues) {
            $filterColumn = $report->reportFilter()->where('question_id', $report->column)->whereNotNull('default_answer')->first();
            $columnValues = $report->columnValues()->where('result_id', $projectResult->id)->orderBy('answer_id');
            if (isset($filterColumn)) {
                $filter = explode('|', $filterColumn->default_answer);
                $columnValues = $columnValues->whereIn('values', $filter);
            }
            $columnListValues = $columnValues->get();
            if ($report->reportFilter()->count() > 0 && !empty($result)) {
                $date = array();
                foreach ($result as $key => $value) {
                    $date_value = $value[$keyDate][1];
                    $date[$key] = $date_value;
                }

                if (!empty($date)) {
                    $lastColumn = $columnValues->orderBy('values', 'desc')->first();
                    $lastColumn->values = max($date);
                }
            }else{
                $lastColumn = $columnValues->orderBy('values', 'desc')->first();
            }
            if ($report->type == Report::TYPE_BAR
                && $report->columnQuestion->code == Report::SORT_BY_DATE
                && (
                    isset($lastColumn)
                    && strtotime($lastColumn->values) !== false
                    )
                ) {
                    $columnListValues = $columnValues->orderBy('values', 'asc');
            }

            $generatedValue['column'] = [];
            foreach ($columnListValues as $key => $value) {
                $generatedValue['column'][$value->values] = $value->values;
            }

            if ($report->type == Report::TYPE_BAR
                && $report->columnQuestion->code == Report::SORT_BY_DATE
                && (
                    isset($lastColumn)
                    && strtotime($lastColumn->values) !== false
                    )
                ) {
                $startFromEndDate = Carbon::parse($lastColumn->values);
                $endDate = Carbon::parse($lastColumn->values);
                $startOfMonth = $startFromEndDate->startOfMonth();
                $endOfMonth = $endDate->endOfMonth();
                $period = CarbonPeriod::create($startOfMonth, $endOfMonth);
                if (isset($period)) {
                    $range = [];
                    foreach ($period->toArray() as $key => $value) {
                        $range[$key] = $value->format('Y-m-d H:i:s');
                    }
                    $generatedValue['column'] = $range;
                }
            }
        }

        // If none of the values exists, return false
        if (!$generatedValue['row'] || !$generatedValue['column']) {
            return false;
        }

        $respondColumn = [];
        $respondTotal = [];

        // Value initial
        foreach ($result as $key => $value) {
            if (isset($value[$rowQuestion]) && isset($value[$columnQuestion])) {
                foreach (Report::operationLabel() as $operation => $label) {
                    foreach ($value[$rowQuestion] as $row) {
                        $resultRow[$row][$operation] = 0;
                        $resultRowPercentOfColumn[$row][$operation] = 0;
                        foreach ($value[$columnQuestion] as $col) {
                            $respondColumn[$col][$operation] = 0;
                            $resultColumn[$col][$operation] = 0;
                            $resultValue[$row][$col][$operation] = 0;
                            $resultPercentOfColumn[$row][$col][$operation] = 0;
                        }

                        if (isset($rowQuestion2)) {
                            foreach ($value[$rowQuestion2] as $row2) {
                                $resultRow2[$row2][$row][$operation] = 0;
                                $resultRowPercentOfColumn2[$row2][$row][$operation] = 0;
                            }
                        }
                    }
                    $resultTotal[$operation] = 0;
                    $respondTotal[$operation] = 0;
                }
            }
        }

        // Value calculation
        foreach ($result as $key => $value) {
            if (isset($value[$rowQuestion]) && isset($value[$columnQuestion]) && isset($value[$dataQuestion])) {
                foreach ($value[$rowQuestion] as $row) {
                    $resultRow[$row][Report::OPERATION_SUM] += (double) array_sum($value[$dataQuestion]);
                    $resultRow[$row][Report::OPERATION_COUNT] += 1;
                    $resultRow[$row][Report::OPERATION_AVG] = (double) $resultRow[$row][Report::OPERATION_SUM] / $resultRow[$row][Report::OPERATION_COUNT];
                    foreach ($value[$columnQuestion] as $col) {
                        $resultValue[$row][$col][Report::OPERATION_SUM] += (double) array_sum($value[$dataQuestion]);
                        $resultValue[$row][$col][Report::OPERATION_COUNT] += 1;
                        $resultValue[$row][$col][Report::OPERATION_AVG] = (double) $resultValue[$row][$col][Report::OPERATION_SUM] / $resultValue[$row][$col][Report::OPERATION_COUNT];

                        $resultColumn[$col][Report::OPERATION_SUM] += (double) array_sum($value[$dataQuestion]);
                        $resultColumn[$col][Report::OPERATION_COUNT] += 1;
                        $resultColumn[$col][Report::OPERATION_AVG] = (double) $resultColumn[$col][Report::OPERATION_SUM] / $resultColumn[$col][Report::OPERATION_COUNT];

                        foreach (Report::operationLabel() as $operation => $label) {
                            $resultRow[$row][$operation] = is_float(round($resultRow[$row][$operation], 2)) ? round($resultRow[$row][$operation], 2) : round($resultRow[$row][$operation]);
                            $resultColumn[$col][$operation] = is_float(round($resultColumn[$col][$operation], 2)) ? round($resultColumn[$col][$operation], 2) : round($resultColumn[$col][$operation]);
                            $resultValue[$row][$col][$operation] = is_float(round($resultValue[$row][$col][$operation], 2)) ? round($resultValue[$row][$col][$operation], 2) : round($resultValue[$row][$col][$operation]);
                        }
                    }

                    if (isset($rowQuestion2)) {
                        if (isset($value[$dataQuestion2])) {
                            foreach ($value[$rowQuestion2] as $row2) {
                                $resultRow2[$row2][$row][Report::OPERATION_SUM] += (double) array_sum($value[$dataQuestion2]);
                                $resultRow2[$row2][$row][Report::OPERATION_COUNT] += 1;
                                $resultRow2[$row2][$row][Report::OPERATION_AVG] = (double) $resultRow2[$row2][$row][Report::OPERATION_SUM] / $resultRow2[$row2][$row][Report::OPERATION_COUNT];

                                foreach (Report::operationLabel() as $operation => $label) {
                                    $resultRow2[$row2][$row][$operation] = is_float(round($resultRow2[$row2][$row][$operation], 2)) ? round($resultRow2[$row2][$row][$operation], 2) : round($resultRow2[$row2][$row][$operation]);
                                }    
                            }
                        }
                    }

                    $resultTotal[Report::OPERATION_SUM] += (double) array_sum($value[$dataQuestion]);
                    $resultTotal[Report::OPERATION_COUNT] += 1;
                    $resultTotal[Report::OPERATION_AVG] = (double) $resultTotal[Report::OPERATION_SUM] / $resultTotal[Report::OPERATION_COUNT];

                    foreach (Report::operationLabel() as $operation => $label) {
                        $resultTotal[$operation] = is_float(round($resultTotal[$operation], 2)) ? round($resultTotal[$operation], 2) : round($resultTotal[$operation]);
                    }
                }
                foreach ($value[$columnQuestion] as $col) {
                    $respondColumn[$col][Report::OPERATION_SUM] += (double) array_sum($value[$dataQuestion]);
                    $respondColumn[$col][Report::OPERATION_COUNT] += 1;
                    $respondColumn[$col][Report::OPERATION_AVG] = (double) $respondColumn[$col][Report::OPERATION_SUM] / $respondColumn[$col][Report::OPERATION_COUNT];

                    foreach (Report::operationLabel() as $operation => $label) {
                        $respondColumn[$col][$operation] = is_float(round($respondColumn[$col][$operation], 2)) ? round($respondColumn[$col][$operation], 2) : round($respondColumn[$col][$operation]);
                    }
                }
                $respondTotal[Report::OPERATION_SUM] += (double) array_sum($value[$dataQuestion]);
                $respondTotal[Report::OPERATION_COUNT] += 1;
                $respondTotal[Report::OPERATION_AVG] = (double) $respondTotal[Report::OPERATION_SUM] / $respondTotal[Report::OPERATION_COUNT];

                foreach (Report::operationLabel() as $operation => $label) {
                    $respondTotal[$operation] = is_float(round($respondTotal[$operation], 2)) ? round($respondTotal[$operation], 2) : round($respondTotal[$operation]);
                }
            }
        }

        // Percentage calculation
        foreach ($result as $key => $value) {
            if (isset($value[$rowQuestion]) && isset($value[$columnQuestion]) && isset($value[$dataQuestion])) {
                foreach ($value[$rowQuestion] as $row) {
                    $resultRowPercentOfColumn[$row][Report::OPERATION_SUM] = @($resultRow[$row][Report::OPERATION_SUM] / $resultTotal[Report::OPERATION_SUM]) * 100;
                    $resultRowPercentOfColumn[$row][Report::OPERATION_AVG]= @($resultRow[$row][Report::OPERATION_AVG] / $resultTotal[Report::OPERATION_AVG]) * 100;
                    $resultRowPercentOfColumn[$row][Report::OPERATION_COUNT] = @($resultRow[$row][Report::OPERATION_COUNT] / $respondTotal[Report::OPERATION_COUNT]) * 100;
                    foreach ($value[$columnQuestion] as $col) {
                        $resultPercentOfColumn[$row][$col][Report::OPERATION_SUM] = @($resultValue[$row][$col][Report::OPERATION_SUM] / $resultColumn[$col][Report::OPERATION_SUM]) * 100;
                        $resultPercentOfColumn[$row][$col][Report::OPERATION_AVG] = @($resultValue[$row][$col][Report::OPERATION_AVG] / $resultColumn[$col][Report::OPERATION_AVG]) * 100;
                        $resultPercentOfColumn[$row][$col][Report::OPERATION_COUNT] = @($resultValue[$row][$col][Report::OPERATION_COUNT] / $respondColumn[$col][Report::OPERATION_COUNT]) * 100;

                        foreach (Report::operationLabel() as $operation => $label) {
                            $resultRowPercentOfColumn[$row][$operation] = is_float(round($resultRowPercentOfColumn[$row][$operation], 2)) ? round($resultRowPercentOfColumn[$row][$operation], 2) : round($resultRowPercentOfColumn[$row][$operation]);
                            $resultPercentOfColumn[$row][$col][$operation] = is_float(round($resultPercentOfColumn[$row][$col][$operation], 2)) ? round($resultPercentOfColumn[$row][$col][$operation], 2) : round($resultPercentOfColumn[$row][$col][$operation]);
                        }
                    }
                }
                if (isset($rowQuestion2)) {
                    foreach ($value[$rowQuestion2] as $row2) {
                        $resultRowPercentOfColumn2[$row2][$row][Report::OPERATION_SUM] = @($resultRow2[$row2][$row][Report::OPERATION_SUM] / $resultTotal[Report::OPERATION_SUM]) * 100;
                        $resultRowPercentOfColumn2[$row2][$row][Report::OPERATION_AVG]= @($resultRow2[$row2][$row][Report::OPERATION_AVG] / $resultTotal[Report::OPERATION_AVG]) * 100;
                        $resultRowPercentOfColumn2[$row2][$row][Report::OPERATION_COUNT] = @($resultRow2[$row2][$row][Report::OPERATION_COUNT] / $respondTotal[Report::OPERATION_COUNT]) * 100;

                        foreach (Report::operationLabel() as $operation => $label) {
                            $resultRowPercentOfColumn2[$row2][$row][$operation] = is_float(round($resultRowPercentOfColumn2[$row2][$row][$operation], 2)) ? round($resultRowPercentOfColumn2[$row2][$row][$operation], 2) : round($resultRowPercentOfColumn2[$row2][$row][$operation]);
                        }
                    }
                }
            }
        }
        
        if (count($generatedValue) > 0) {
            $generatedValue['column'] = @array_values($generatedValue['column']);
            $generatedValue['row'] = @array_values($generatedValue['row']);
        }

        $data['result'] = $result;
        $data['resultSummary'] = $resultSummary;
        $data['resultColumn'] = $resultColumn;
        $data['resultRow'] = $resultRow;
        $data['resultRow2'] = $resultRow2;
        $data['resultValue'] = $resultValue;
        $data['resultTotal'] = $resultTotal;
        $data['resultRowPercentOfColumn'] = $resultRowPercentOfColumn;
        $data['resultRowPercentOfColumn2'] = $resultRowPercentOfColumn2;
        $data['resultPercentOfColumn'] = $resultPercentOfColumn;
        $data['respondColumn'] = $respondColumn;
        $data['respondTotal'] = $respondTotal;
        $data['generatedValue'] = $generatedValue;
        $data['jsonValue'] = $this->jsonValue($report, $data, $request);

        return $data;
    }

    private function filterResult($report, &$result)
    {
        foreach ($report->reportFilter()->whereNotNull('default_answer')->get() as $key => $filter) {
            $answer = explode('|', $filter->default_answer);
            $alias = $filter->question->alias;
            $result = $result->filter(function ($collection) use ($answer, $alias) {
                return array_intersect($answer, isset($collection[$alias])?$collection[$alias]:array());
            });
        }
    }

    private function filterResultSummary($report, &$result)
    {
        foreach ($report->reportFilterSummary()->whereNotNull('default_answer')->get() as $key => $filter) {
            $answer = explode('|', $filter->default_answer);
            $alias = $filter->question->alias;
            $result = $result->filter(function ($collection) use ($answer, $alias) {
                return array_intersect($answer, isset($collection[$alias])?$collection[$alias]:array());
            });
        }
    }

    private function jsonValue($report, $data, Request $request)
    {
        $generatedValue = $data['generatedValue'];
        $resultValue = $data['resultValue'];
        $resultColumn = $data['resultColumn'];
        $resultPercentOfColumn = $data['resultPercentOfColumn'];
        $resultValue2 = $data['resultRow2'];
        $resultRowPercentOfColumn2 = $data['resultRowPercentOfColumn2'];

        if (!$generatedValue) {
            return;
        }
        
        $dataset = [];
        $dataset2 = [];
        if ($report->type == Report::TYPE_BAR_LINE) {
            $index = 0;
            $indexRow = 0;
            foreach ($generatedValue['column'] as $column) {
                $dataset['default'][$index]['name'] = $column;

                foreach ($generatedValue['row'] as $key => $row) {
                    $dataset['default'][$index]['labels'][$indexRow] = $column .' - '. $row;
                    $indexRow++;
                }
            }
            $dataset2 = $dataset;

            //dataset #1
            $index = 0;
            $indexRow = 0;
            foreach ($generatedValue['column'] as $column) {
                foreach ($generatedValue['row'] as $key => $row) {
                    if (isset($resultValue[$row][$column]) && isset($resultPercentOfColumn[$row][$column])) {
                        if ($report->showvalues == Report::SHOW_PERCENTAGE) {
                            if ($report->showvalues == Report::SHOW_PERCENTAGE) {
                                $dataset['default'][$index]['values'][$indexRow] = (double) ($resultPercentOfColumn[$row][$column][$report->operation] / 100);
                            } else {
                                $dataset['default'][$index]['values'][$indexRow] = (double) $resultPercentOfColumn[$row][$column][$report->operation];
                            }
                        } else {
                            $dataset['default'][$index]['values'][$indexRow] = (double) $resultValue[$row][$column][$report->operation];
                        }
                    } else {
                        $dataset['default'][$index]['values'][$indexRow] = 0;
                    }
                    $indexRow++;
                }
            }

            //dataset #2
            $index = 0;
            $indexRow = 0;
            foreach ($generatedValue['column'] as $column) {
                foreach ($generatedValue['row'] as $key => $row) {
                    if (isset($resultValue2[$column][$row]) && isset($resultRowPercentOfColumn2[$column][$row])) {
                        if ($report->showvalues == Report::SHOW_PERCENTAGE) {
                            if ($report->showvalues == Report::SHOW_PERCENTAGE) {
                                $dataset2['default'][$index]['values'][$indexRow] = (double) ($resultRowPercentOfColumn2[$column][$row][$report->operation] / 100);
                            } else {
                                $dataset2['default'][$index]['values'][$indexRow] = (double) $resultRowPercentOfColumn2[$column][$row][$report->operation];
                            }
                        } else {
                            $dataset2['default'][$index]['values'][$indexRow] = (double) $resultValue2[$column][$row][$report->operation];
                        }
                    } else {
                        $dataset2['default'][$index]['values'][$indexRow] = 0;
                    }
                    $indexRow++;
                }
            }
        } else {
            $index = 0;
            $indexRow = 0;    
            foreach ($generatedValue['row'] as $key => $row) {
                $dataset['default'][$index]['name'] = $row;
                
                $indexCol = 0;
                foreach ($generatedValue['column'] as $col => $column) {
                    if ($report->type == Report::TYPE_BAR && $report->columnQuestion->code == Report::SORT_BY_DATE && strtotime($column) !== false) {
                        $dataset['default'][$index]['labels'][$indexCol] = date('d/m', strtotime($column));
                    } else {
                        $dataset['default'][$index]['labels'][$indexCol] = $column;
                    }
                    if (isset($resultValue[$row][$column]) && isset($resultPercentOfColumn[$row][$column])) {
                        if ($report->showvalues == Report::SHOW_PERCENTAGE) {
                            if ($report->showvalues == Report::SHOW_PERCENTAGE) {
                                $dataset['default'][$index]['values'][$indexCol] = (double) ($resultPercentOfColumn[$row][$column][$report->operation] / 100);
                            } else {
                                $dataset['default'][$index]['values'][$indexCol] = (double) $resultPercentOfColumn[$row][$column][$report->operation];
                            }
                        } else {
                            $dataset['default'][$index]['values'][$indexCol] = (double) $resultValue[$row][$column][$report->operation];
                        }
                    } else {
                        $dataset['default'][$index]['values'][$indexCol] = 0;
                    }
                    $indexCol++;
                }
                $index++;
            }
        }

        $index = 0;
        foreach ($generatedValue['column'] as $column) {
            $dataset['pie'][0]['labels'][$index] = $column;
            if (isset($resultColumn[$column])) {
                $dataset['pie'][0]['values'][$index] = (double) $resultColumn[$column][$report->operation];
            } else {
                $dataset['pie'][0]['values'][$index] = 0;
            }
            $index++;
        }
        $json = [
            'default' => json_encode($dataset['default']),
            'default2' => (isset($dataset2['default']))?json_encode($dataset2['default']):[],
            'pie' => json_encode($dataset['pie']),
        ];

        return $json;
    }

    public function save(GenerateReportRequest $request)
    {
        \DB::beginTransaction();
        $auth = \Auth::user();
        try {
            $project = $this->projectModel->where('uuid', $request->projectid)->first();

            $report = $this->reportModel->firstOrNew(['uuid' => $request->reportid]);
            $report->uuid = $report->exists ? $request->reportid : \Uuid::generate();
            $report->project_id = $project->id;
            $report->name = $request->reportname;
            $report->type = $request->type;
            $report->row = $request->side;
            $report->column = $request->top;
            $report->data = $request->value;
            $report->operation = $request->operation;
            $report->row_combo2 = $request->side_combo2;
            $report->data_combo2 = $request->data_combo2;
            $report->operation_combo2 = $request->operation_combo2;
            $report->user_id = \Auth::user()->id;
            if (!$report->exists) {
                $report->created_by = $auth->id;
            }
            $report->updated_by = $auth->id;
            
            $report->save();

            \DB::commit();

            return redirect()->route('report.generate', ['project_id' => $project->uuid, 'report' => $report->uuid])->with([
                'code' => 200,
                'message' => 'Generated report has been saved.'
            ]);
        } catch (Exception $e) {
            \DB::rollback();

            return redirect()->back()->withErrors($e)->withInput()->with([
                'code' => 400,
                'message' => 'Failed to save generated report.'
            ]);
        }
    }

    public function saveTrendline(ReportTrendlineRequest $request)
    {
        \DB::beginTransaction();
        
        try {
            $project = $this->projectModel->where('uuid', $request->projectid)->first();
            $report = $this->reportModel->firstOrNew(['uuid' => $request->reportid]);
            if($report){
                $report->trendline = $request->trendline;
                
                $report->save();
    
                \DB::commit();
    
                return redirect()->route('report.generate', ['project_id' => $project->uuid, 'report' => $report->uuid])->with([
                    'code' => 200,
                    'message' => 'Generated report has been saved.'
                ]);
            }else{
                return redirect()->route('project.detail', ['project_id' => $project->uuid])->with([
                    'code' => 400,
                    'message' => 'Report Is Already Deleted by Admin !'
                ]); 
            }
        } catch (Exception $e) {
            \DB::rollback();

            return redirect()->back()->withErrors($e)->withInput()->with([
                'code' => 400,
                'message' => 'Failed to save generated report.'
            ]);
        }
    }

    public function saveSummary(Request $request)
    {
        \DB::beginTransaction();
        
        try {
            $project = $this->projectModel->where('uuid', $request->projectid)->first();
            $report = $this->reportModel->firstOrNew(['uuid' => $request->reportid]);
            if($report){
                $report->summary = $request->summary;
                
                $report->save();
    
                \DB::commit();
    
                return redirect()->route('report.generate', ['project_id' => $project->uuid, 'report' => $report->uuid])->with([
                    'code' => 200,
                    'message' => 'Generated report has been saved.'
                ]);
            }else{
                return redirect()->route('project.detail', ['project_id' => $project->uuid])->with([
                    'code' => 400,
                    'message' => 'Report Is Already Deleted by Admin !'
                ]); 
            }
        } catch (Exception $e) {
            \DB::rollback();

            return redirect()->back()->withErrors($e)->withInput()->with([
                'code' => 400,
                'message' => 'Failed to save generated report.'
            ]);
        }
    }

    public function saveShowValues(Request $request)
    {
        \DB::beginTransaction();
        
        try {
            $project = $this->projectModel->where('uuid', $request->projectid)->first();
            $report = $this->reportModel->firstOrNew(['uuid' => $request->reportid]);
            if($report){
                $report->showvalues = $request->showValues;
                
                $report->save();
    
                \DB::commit();
    
                return redirect()->route('report.generate', ['project_id' => $project->uuid, 'report' => $report->uuid])->with([
                    'code' => 200,
                    'message' => 'Generated report has been saved.'
                ]);
            }else{
                return redirect()->route('project.detail', ['project_id' => $project->uuid])->with([
                    'code' => 400,
                    'message' => 'Report Is Already Deleted by Admin !'
                ]); 
            }
        } catch (Exception $e) {
            \DB::rollback();

            return redirect()->back()->withErrors($e)->withInput()->with([
                'code' => 400,
                'message' => 'Failed to save generated report.'
            ]);
        }
    }

    public function delete($projectId, $reportId)
    {
        \DB::beginTransaction();
        
        try {
            $report = $this->reportModel->where('uuid', $reportId)->first();
            $report->delete();

            \DB::commit();

            return redirect()->route('project.detail', ['uuid' => $projectId])->with([
                'code' => 200,
                'message' => 'Generated report has been deleted.'
            ]);
        } catch (Exception $e) {
            \DB::rollback();

            return redirect()->route('project.detail', ['uuid' => $projectId])->withErrors($e)->withInput()->with([
                'code' => 400,
                'message' => 'Failed to delete generated report.'
            ]);
        }
    }

    public function exportToExcel($projectId, $reportId, Request $request)
    {
        $project = $this->projectModel->where('uuid', $projectId)->with('projectQuestion')->first();
        $report = $this->reportModel->where('uuid', $reportId)->first();
        $generatedValue = $this->generateValueReport($project, $report, $request);
        Excel::create(date('dmY_His').'-'.str_slug($report->name), function ($excel) use ($project, $report, $generatedValue) {

            // Set the title
            $excel->setTitle($report->name);
        
            // Chain the setters
            $excel->setCreator(config('app.APP_NAME'))
                  ->setCompany(config('app.APP_NAME'));
        
            // Call them separately
            $excel->setDescription($report->name);
        
            $excel->sheet('Sheet1', function ($sheet) use ($project, $report, $generatedValue) {
                $data = [];
                $data['project'] = $project;
                $data['report'] = $report;
        
                $data = array_merge($data, $generatedValue);
                
                $sheet->loadView('report.type.table', $data);
            });
        })->export('xlsx');
    }

    public function exportToPDF($projectId, $reportId, Request $request)
    {
        $project = $this->projectModel->where('uuid', $projectId)->with('projectQuestion')->first();
        $report = $this->reportModel->where('uuid', $reportId)->first();
        $generatedValue = $this->generateValueReport($project, $report, $request);
        
        $data = [];
        $data['project'] = $project;
        $data['report'] = $report;
        $data = array_merge($data, $generatedValue);
        
        $pdf = PDF::loadView('report.type.table', $data);

        return $pdf->download(date('dmY_His').'-'.str_slug($report->name).'.pdf');
    }

    public function questionList($projectId, Request $request)
    {
        $user = Auth::user();
        $project = $this->projectModel->where('uuid', $projectId)->with('projectQuestion')->first();
        $questionList = $project->projectQuestion();

        if ($request->questionId) {
            $questionList = $questionList->whereNotIn('id', $request->questionId);
        }
        
        if ($request->search) {
            $questionList = $questionList->where('question', 'LIKE', '%'.$request->search.'%');
        }

        if ($user->role != User::ROLE_ADMIN) {
            $questionList = $questionList->where('visibleFilter', 1);
        }
        $countFiltered = $questionList->count();

        // $questionList = $questionList->get();
        $questionList = $questionList->paginate(10);

        $question = [];
        foreach ($questionList as $key => $value) {
            $question[$key]['id'] = $value->id;
            $question[$key]['text'] = $value->alias;
        }

        $data = [
            'results' => $question,
            'count_filtered' => $countFiltered,
        ];
        
        return response()->json($data);
    }

    public function resultList($projectId, $questionId, Request $request)
    {
        $project = $this->projectModel->where('uuid', $projectId)->first();
        $projectResult = $project->projectResult()->orderBy('id', 'desc')->first();
        if (!$projectResult || !$projectResult->projectResultValue) {
            return response()->json([
                'results' => null,
                'count_filtered' => null,
            ]);
        }

        $resultList = $projectResult->projectResultValue()
        ->select([\DB::raw('distinct(`values`) as `values`')])
        ->where('project_id', $project->id)
        ->where('question_id', $questionId)
        ->orderBy('values')
        ;

        if ($request->search) {
            $resultList = $resultList->where('values', 'LIKE', '%'.$request->search.'%');
        }
        $countFiltered = $resultList->count();

        // $resultList = $resultList->get();
        $resultList = $resultList->paginate(10);
        
        $result = [];
        $index = 0;
        foreach ($resultList as $key => $value) {
            $result[$index]['id'] = $value->values;
            $result[$index]['text'] = $value->values;
            $index++;
        }

        $data = [
            'results' => $result,
            'count_filtered' => $countFiltered,
        ];
        
        return response()->json($data);
    }

    public function saveFilter($reportId, $projectId, Request $request)
    {
        $user = Auth::user();
        \DB::beginTransaction();
        
        try {
            $report = $this->reportModel->where('uuid', $reportId)->first();

            if($report){
                $report->reportFilter()->delete();

                $question = $request->filterQuestion;
                $answer = $request->filterAnswer;
                $filterUser = $request->filterUserId;
                if ($question) {
                    foreach ($question as $key => $value) {
                        if (!empty($value)) {
                            $filter = $report->reportFilter()->create([
                                'project_id' => $report->project_id,
                                'report_id' => $report->id,
                                'question_id' => $value,
                                'user_id' => isset($filterUser[$key]) && !empty($filterUser[$key]) ? $filterUser[$key] : $user->id,
                            ]);
                            if (isset($answer[$key])) {
                                $filter->update([
                                    'default_answer' => implode('|', $answer[$key]),
                                ]);
                            }
                        }
                    }
                }
    
                \DB::commit();
    
                return redirect()->route('report.generate', ['project_id' => $report->project->uuid, 'report' => $report->uuid])->with([
                    'code' => 200,
                    'message' => 'Filter report has been saved.'
                ]);
            }else{
                return redirect()->route('project.detail', ['project_id' => $projectId])->with([
                    'code' => 400,
                    'message' => 'Report Is Already Deleted by Admin !'
                ]);                
            }
        } catch (Exception $e) {
            \DB::rollback();

            return redirect()->back()->withErrors($e)->withInput()->with([
                'code' => 400,
                'message' => 'Failed to save filter report.'
            ]);
        }
    }

    public function saveSummaryTable(Request $request)
    {
        if (count($request->summary) < 1) {
            $report = $this->reportModel->where('uuid', $request->report_id)->first();
            if($report){
                $report->reportSummary()->delete();
    
                return redirect()->route('report.generate', ['project_id' => $report->project->uuid, 'report' => $report->uuid])->with([
                    'code' => 200,
                    'message' => 'Generated report has been saved.'
                ]);
            }else{
                return redirect()->route('project.detail', ['project_id' => $request->project_id])->with([
                    'code' => 400,
                    'message' => 'Report Is Already Deleted by Admin !'
                ]);
            }
        }
        \DB::beginTransaction();
        
        try {
            $report = $this->reportModel->where('uuid', $request->report_id)->first();
            if($report){
                $report->show_summary = $request->toggle_summary;
                $report->save();
    
                $report->reportSummary()->delete();
    
                foreach ($request->summary as $key => $value) {
                    $summary = $report->reportSummary()->create([
                        'report_id' => $report->id,
                        'question_id' => $value,
                    ]);
                }
    
                \DB::commit();
    
                return redirect()->route('report.generate', ['project_id' => $report->project->uuid, 'report' => $report->uuid])->with([
                    'code' => 200,
                    'message' => 'Generated report has been saved.'
                ]);
            }else{
                return redirect()->route('project.detail', ['project_id' => $request->project_id])->with([
                    'code' => 400,
                    'message' => 'Report Is Already Deleted by Admin !'
                ]);
            }
        } catch (Exception $e) {
            \DB::rollback();

            return redirect()->back()->withErrors($e)->withInput()->with([
                'code' => 400,
                'message' => 'Failed to save generated report.'
            ]);
        }
    }
}
