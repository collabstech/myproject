<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Project;
use App\ProjectQuestionAnswer;
use App\ProjectResultValue;
use App\Report;
use App\User;

use DataTables;
use Auth;

class ReportSummaryController extends Controller
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
        )
    {
        $this->reportModel = $report;
        $this->projectModel = $project;
        $this->answerModel = $answer;
        $this->valueModel = $value;
    }

    public function summaryTable(Request $request)
    {
        $project = $this->projectModel->where('uuid', $request->project_id)->with('projectQuestion')->first();
        $report = $this->reportModel->where('uuid', $request->report_id)->first();

        $reportFilterSummary = [];
        $filterAnswer = [];
        if (isset($report->reportFilterSummary)) {
            $reportFilterSummary = $report->reportFilterSummary;
            foreach ($reportFilterSummary as $key => $value) {
                $filterAnswer[$value->question_id] = explode('|', $value->default_answer);
            }
        }

        $summaryField = [];
        if (isset($report->reportSummary)) {
            $reportSummary = $report->reportSummary;
            foreach ($reportSummary as $key => $value) {
                $summaryField[$value->question_id] = $value->question_id;
            }
        }

        $questionList = $project->projectQuestion();
        $questionArray = [];
        foreach ($questionList->get() as $key => $value) {
            $questionArray[$value->id] = $value;
        }
        $questionSort = $questionList->where('code', Report::SORT_BY_DATE)->first();

        $result = $this->generateResult($project, $report, $questionArray, $request);
        $result = $result->sortByDesc($questionSort->alias);

        $data = [];
        $data['questionList'] = $questionList;
        $data['questionArray'] = $questionArray;
        $data['summaryField'] = $summaryField;
        $data['resultSummary'] = $result;

        return view('report.summary.table', $data);
    }

    private function generateResult($project, $report, $questionArray, Request $request)
    {
        $projectResult = $project->projectResult()->orderBy('id', 'desc')->first();
        if (!$projectResult || !$projectResult->has('projectResultValue')) {
            return [];
        }

        $resultItem = $projectResult->projectResultValue()
                                    ->select(['id', 'row', 'question_id', 'answer_column', 'values'])
                                    ->get();

        $res = [];
        foreach ($resultItem as $key => $value) {
            $alias = $questionArray[$value->question_id]->alias;
            $res[$value->row][$alias][$value->answer_column] = $value->values;
        }

        $result = [];
        $result = collect($res);
        if ($report->reportFilter()->count() > 0) {
            $this->filterResultMain($report, $result);
        }
        if ($report->reportFilterSummary()->count() > 0) {
            $this->filterResult($report, $result);
        }

        $this->filterResultByChart($report, $result, $request);

        return $result;
    }

    private function filterResultMain($report, &$result)
    {
        foreach ($report->reportFilter()->whereNotNull('default_answer')->get() as $key => $filter) {
            $answer = explode('|', $filter->default_answer);
            $alias = $filter->question->alias;
            $result = $result->filter(function($collection) use ($answer, $alias) { 
                return array_intersect($answer, !empty($collection[$alias])?$collection[$alias]:array());
            });
        }
    }

    private function filterResult($report, &$result)
    {
        foreach ($report->reportFilterSummary()->whereNotNull('default_answer')->get() as $key => $filter) {
            $answer = explode('|', $filter->default_answer);
            $alias = $filter->question->alias;
            $result = $result->filter(function($collection) use ($answer, $alias) { 
                return array_intersect($answer, !empty($collection[$alias])?$collection[$alias]:array());
            });
        }
    }

    private function filterResultByChart($report, &$result, Request $request)
    {
        $columnValues = $report->columnQuestion->alias;
        $rowValues = $report->rowQuestion->alias;
        $topExplode = explode('-',$request->top);
        if(count($topExplode) > 1){
            $top = trim($topExplode[0]);
            $side = trim($topExplode[1]);
        }else{
            $top = $request->top;
            $side = $request->side;
        }

        $result = $result->filter(function($collection) use ($top, $columnValues) { 
            return array_intersect([$top], !empty($collection[$columnValues])?$collection[$columnValues]:array());
        });

        $result = $result->filter(function($collection) use ($side, $rowValues) { 
            return array_intersect([$side], !empty($collection[$rowValues])?$collection[$rowValues]:array());
        });
    }

    public function saveFilterSummary($reportId, $projectId, Request $request)
    {
        $user = Auth::user();
        \DB::beginTransaction();
        
        try {
            $report = $this->reportModel->where('uuid', $reportId)->first();
            if($report){
                $report->reportFilterSummary()->delete();
    
                $question = $request->filterQuestionSummary;
                $answer = $request->filterAnswerSummary;
                $filterUser = $request->filterUserId;
                if ($question) {
                    foreach ($question as $key => $value) {
                        if (!empty($value)) {
                            $filter = $report->reportFilterSummary()->create([
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
                    'code' => 200,
                    'message' => 'Report Deleted by Admin !'
                ]);              
            }
        } catch (Exception $e) {
            \DB::rollback();

            return redirect()->back()->withErrors($e)->withInput()->with([
                'code' => 400,
                'message' => 'Failed to save filter report summary.'
            ]);
        }
    }
}