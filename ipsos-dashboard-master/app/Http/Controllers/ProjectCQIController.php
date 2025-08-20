<?php

namespace App\Http\Controllers;

use Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Auth;

use Box\Spout\Reader\ReaderFactory;
use Box\Spout\Common\Type;
use Box\Spout\Reader\XLSX\Sheet;

class ProjectCQIController extends ProjectController
{
    public function importCQIProgress($uuid, Request $request, $deleteAndInsert = true) 
    {
        if (Auth::user()->role != \App\User::ROLE_ADMIN) return;

        $project = \App\Project::where('uuid', $uuid)->first();

        $validator = Validator::make($request->all(), [
            'import_progress' => 'required|file|mimes:xlsx'
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'File type must be xlsx'], 422);
        }

        if ($request->import_progress) {
            $path = \Storage::putFileAs('progress-CQI', $request->file('import_progress'), $request->import_progress->getClientOriginalName());
            $reader = ReaderFactory::create(Type::XLSX);
            $reader->open(storage_path('app/' . $path));

            if ($deleteAndInsert) {
                \App\ProgressCQIData::where('project_id', $project->id)->delete();
            }
            
            foreach ($reader->getSheetIterator() as $sheet) {
                if (!$this->validateCQIProgressSheet($sheet)) {
                    continue;
                }
                
                foreach ($sheet->getRowIterator() as $keyRow => $row) {
                    if ($keyRow < 2) {
                        continue;
                    }

                    if ($keyRow > 2) {
                        break;
                    }

                    $typeKey = 2;
                    if ($row[$typeKey] == '' || $row[$typeKey] == null) {
                        return response()->json(['message' => 'File is not valid'], 422);
                    }

                    $startKey = $typeKey;
                    $typeKey += 3;
                    $isValidToRead = true;
                    while ($isValidToRead) {
                        $type = $row[$typeKey];

                        if ($type == '' || $type == null) {
                            $typeKey += 3;
                        }
                        else {
                            $this->storeCQIProgressData($project->id, $row[$startKey], $sheet, $startKey, $typeKey - 1);
                            $startKey = $typeKey;
                            $typeKey += 3;
                        }

                        if ($type == 'TOTAL') {
                            $isValidToRead = false;
                        }
                    }
                }
            }

            $reader->close();
            return response()->json(['message' => 'Success'], 200);
        }
    }

    private function validateCQIProgressSheet(Sheet $sheet) 
    {
        $isValid = false;
        foreach ($sheet->getRowIterator() as $keyRow => $row) {
            if ($keyRow == 2 && $row[0] == 'Main Dealer' && $row[1] == 'Kota/Kabupaten') {
                $isValid = true;
            }
            else {
                $isValid = false;
            }

            if ($keyRow == 4 && $row[2] == 'Target' && $row[3] == 'Actual' && $row[4] == '%age actual') {
                $isValid = true;
                break;
            }
            else {
                $isValid = false;
            }
        }
        return $isValid;
    }

    private function storeCQIProgressData($projectId, $type, $sheet, $startIndex, $endIndex) 
    {
        $modelRow = null;
        foreach ($sheet->getRowIterator() as $keyRow => $row) {
            if ($keyRow < 5) {
                if ($keyRow == 3) {
                    $modelRow = $row;
                }
                continue;
            }

            if ($row[0] == 'TOTAL' || $row[0] == '') continue;

            for ($i = $startIndex; $i <= $endIndex; $i += 3) {
                $progressCQIData = \App\ProgressCQIData::where('project_id', $projectId)
                        ->where('main_dealer', $row[0])
                        ->where('district', $row[1])
                        ->where('type', $type)
                        ->where('model', $modelRow[$i])
                        ->first();
                
                if (!$progressCQIData) {
                    $progressCQIData = new \App\ProgressCQIData;
                    $progressCQIData->project_id = $projectId;
                    $progressCQIData->main_dealer = $row[0];
                    $progressCQIData->district = $row[1];
                    $progressCQIData->type = $type;
                    $progressCQIData->model = $modelRow[$i];
                }

                $progressCQIData->target = filter_var($row[$i], FILTER_VALIDATE_INT) === false ? 0 : $row[$i];
                $progressCQIData->actual = filter_var($row[$i + 1], FILTER_VALIDATE_INT) === false ? 0 : $row[$i + 1];

                $progressCQIData->save();
            }
        }
    }

    public function getCQIMotorcycleType($uuid)
    {
        if (!(Auth::user()->role == \App\User::ROLE_ADMIN || Auth::user()->role == \App\User::ROLE_CLIENT)) {
            return response()->json(['message' => 'Unauthorized'], 400);
        }

        $project = \App\Project::where('uuid', $uuid)->first();

        if (Auth::user()->role == \App\User::ROLE_CLIENT) {
            $userProject = DB::table('user_projects')
                    ->where('user_id', Auth::user()->id)
                    ->where('project_id', $project->id)
                    ->get();
            
            if ($userProject == null || sizeof($userProject) < 1) {
                return response()->json(['message' => 'Unauthorized'], 400);
            }
        }

        $types = \App\ProgressCQIData::select('type')
                ->where('project_id', $project->id)
                ->distinct('type')
                ->get();
        
        return response()->json($types, 200);
    }

    public function getCQIMotorcycleModel($uuid, Request $request)
    {
        if (!(Auth::user()->role == \App\User::ROLE_ADMIN || Auth::user()->role == \App\User::ROLE_CLIENT)) {
            return response()->json(['message' => 'Unauthorized'], 400);
        }

        $project = \App\Project::where('uuid', $uuid)->first();

        if (Auth::user()->role == \App\User::ROLE_CLIENT) {
            $userProject = DB::table('user_projects')
                    ->where('user_id', Auth::user()->id)
                    ->where('project_id', $project->id)
                    ->get();
            
            if ($userProject == null || sizeof($userProject) < 1) {
                return response()->json(['message' => 'Unauthorized'], 400);
            }
        }

        $type = $request->type;

        $models = \App\ProgressCQIData::select('model')
                ->where('project_id', $project->id)
                ->where(function($query) use ($type) {
                    if ($type != '0') {
                        $query->where('type', $type);
                    }
                })
                ->distinct('model')
                ->get();
        
        return response()->json($models, 200);
    }

    public function getCQITotalChartData($uuid, Request $request) 
    {
        if (!(Auth::user()->role == \App\User::ROLE_ADMIN || Auth::user()->role == \App\User::ROLE_CLIENT)) {
            return response()->json(['message' => 'Unauthorized'], 400);
        }

        $project = \App\Project::where('uuid', $uuid)->first();

        if (Auth::user()->role == \App\User::ROLE_CLIENT) {
            $userProject = DB::table('user_projects')
                    ->where('user_id', Auth::user()->id)
                    ->where('project_id', $project->id)
                    ->get();
            
            if ($userProject == null || sizeof($userProject) < 1) {
                return response()->json(['message' => 'Unauthorized'], 400);
            }
        }

        $type = $request->type;
        $model = $request->model;
        $chartType = $request->chartType;
        
        if ($chartType == 'total-mmodel' && $type == '0') {
            return response()->json('Please select type', 400);
        }

        $query = \App\ProgressCQIData::query();
        $distinctQuery = \App\ProgressCQIData::query();
        $query->where('project_id', $project->id);
        $distinctQuery->where('project_id', $project->id);

        if ($type != '0') {
            $query->where('type', $type);
            $distinctQuery->where('type', $type);
        }
        if ($model != '0') {
            $query->where('model', $model);
            $distinctQuery->where('model', $model);
        }

        $select = '';
        if ($chartType == 'total-mtype') {
            $query->groupBy('type');
            $select = 'type as label';
        }
        else if ($chartType == 'total-mmodel') {
            $query->groupBy('model');
            $select = 'model as label';
        }
        else {
            $query->groupBy('district');
            $select = 'district as label';
        }

        $query->selectRaw($select . ',
                    sum(target) as target,
                    sum(actual) as actual
                ');

        $progressCQIDatas = $query->get();
        $achievementDatas = array();
        $targetDatas = array();
        $labels = array();

        $totalAchievement = 0;
        $index = 0;
        for ($i = 0; $i < sizeof($progressCQIDatas); ++$i) {
            if ($progressCQIDatas[$i]->label == null || $progressCQIDatas[$i]->label == '') continue;

            $achievement = $progressCQIDatas[$i]->actual;
            $target = $progressCQIDatas[$i]->target;

            $totalAchievement += $achievement;
            $achievementDatas[$index] = $achievement;
            $targetDatas[$index] = $target;
            $labels[$index] = $progressCQIDatas[$i]->label;
            ++$index;
        }

        $distinctLabels = $distinctQuery->selectRaw($select)->distinct()->get();
        foreach ($distinctLabels as $key => $value) {
            $idx = array_search($value->label, $labels);
            if ($key != $idx) {
                $temp = $labels[$key];
                $labels[$key] = $labels[$idx];
                $labels[$idx] = $temp;

                $temp = $targetDatas[$key];
                $targetDatas[$key] = $targetDatas[$idx];
                $targetDatas[$idx] = $temp;

                $temp = $achievementDatas[$key];
                $achievementDatas[$key] = $achievementDatas[$idx];
                $achievementDatas[$idx] = $temp;
            }
        }

        $response = (object) array(
                    'type' => $type,
                    'total_achievement' => $totalAchievement,
                    'target' => $targetDatas,
                    'achievement' => $achievementDatas,
                    'label' => $labels
                );

        return response()->json($response, 200);
    }
}