<?php

namespace App\Http\Controllers;

use App\ExcludeInterviewDate;
use App\Interviewer;
use App\Project;
use App\User;
use Carbon\Carbon;
use DateInterval;
use DatePeriod;
use DateTime;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Auth;

use Box\Spout\Reader\ReaderFactory;
use Box\Spout\Common\Type;
use Box\Spout\Reader\XLSX\Sheet;
use Psy\Exception\ParseErrorException;

class ProjectCSIController extends ProjectController
{
    private $subtractTotalToGetLastIndexNumber = 2;

    public function importInterviewer(Request $request) 
    {
        if (Auth::user()->role != User::ROLE_ADMIN) {
            abort(403);
        }

        $extension = '';

        if($request->import_interviewer) {
            $extension = $request->import_interviewer->getClientOriginalExtension();
        }

        $request->request->add([
            'extension' => $extension,
        ]);

        $validator = Validator::make($request->all(), [
            'import_interviewer' => 'required|file',
            'extension' => 'required|in:xlsx,xls,csv',
            'exclude_date_from' => 'nullable|date',
            'exclude_date_to' => 'nullable|date'
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => "Invalid request data"], 422);
        }

        if ($request->import_interviewer == null) {
            return response()->json(['message' => 'File is required'], 422);
        }

        $this->saveExcludeDate($request);

        $path = Storage::putFileAs('interviewer-csi', $request->file('import_interviewer'), $request->import_interviewer->getClientOriginalName());
        $reader = ReaderFactory::create(Type::XLSX);
        $reader->open(storage_path('app/' . $path));

        // validate
        foreach ($reader->getSheetIterator() as $sheet) {
            if (!$this->validateInterviewerSheet($sheet)) {
                return response()->json(['message' => 'File is not valid. Make sure you do not change file manually'], 422);
            }
        }

        // import
        foreach ($reader->getSheetIterator() as $sheet) {
            if(!$this->importInterviewerData($request->project_id, $sheet)){
                return response()->json(['message' => 'File is not valid. Failed to store data to database'], 422);
            };
        }

        $reader->close();
        return response()->json(['message' => 'Success'], 200);
    }

    private function saveExcludeDate($request) {
        if ($request->exclude_date_from == null || $request->exclude_date_to == null) {
            return;
        }
        ExcludeInterviewDate::where(['project_id' => $request->project_id])->delete();
        if ($request->exclude_date_from === $request->exclude_date_to) {
            ExcludeInterviewDate::create([
                'date' => Carbon::parse($request->exclude_date_from)->format('Y-m-d'),
                'project_id' => $request->project_id
            ]);
            return;
        }

        $dateFrom = new DateTime($request->exclude_date_from);
        $dateTo = new DateTime($request->exclude_date_to);
        $dateTo->modify('+1 day');

        $ranges = new DatePeriod(
            $dateFrom,
            new DateInterval('P1D'),
            $dateTo
        );
        foreach ($ranges as $value) {
            ExcludeInterviewDate::create([
                'date' => $value->format('Y-m-d'),
                'project_id' => $request->project_id
            ]);
        }
    }

    private function validateInterviewerSheet(Sheet $sheet) 
    {
        $isValid = false;
        foreach ($sheet->getRowIterator() as $keyRow => $row) {
            $totalRow = count($row);
            if ($keyRow < 1) {
                continue;
            }
            if ($keyRow > 1) {
                break;
            }
            if ($row[1] == 'MD Code' && $row[2] == 'MD'
                    && $row[3] == 'ID Interviewer' &&
                    $row[$totalRow - $this->subtractTotalToGetLastIndexNumber - 1] == 'Total ACHIEVED Per Itters') {
                $isValid = true;
                break;
            }

        }
        return $isValid;
    }

    private function importInterviewerData($projectId, Sheet $sheet, $deleteAndInsert = true) 
    {        
        if ($deleteAndInsert) {
            Interviewer::where('project_id', $projectId)->delete();
        }
        $dates = array();
        foreach ($sheet->getRowIterator() as $keyRow => $row) {
            if ($keyRow == 1) {
                $i = 5;
                while ($row[$i] != '') {
                    try {
                        $dates[$i] = $row[$i]->format('Y-m-d');
                    }catch (ParseErrorException $e) {
                        return false;
                    }
                    $i++;
                }
            }

            if ($keyRow < 2) {
                continue;
            }
            if ($row[1] == null || $row[1] == '') {
                continue;
            }

            $this->storeInterviewerData($projectId, $row, $dates);

        }

        return true;
    }

    private function storeInterviewerData($projectId, $row, $dates)
    {
        $interviewer = Interviewer::where('project_id', $projectId)
                ->where('main_dealer_code', $row[1])
                ->where('interviewer_id', $row[3])
                ->first();
        if (!$interviewer) {
            $interviewer = new Interviewer;
            $interviewer->project_id = $projectId;
            $interviewer->main_dealer_code = $row[1];
            $interviewer->interviewer_id = $row[3];
        }

        $i = 5;
        if ($dates === []){
            return;
        }

        while ($row[$i] !== '') {
            $interviewerClone = clone $interviewer;
            $interviewerClone->main_dealer_name = $row[2];
            $interviewerClone->achievement_percent = 0;

            if ($interviewerClone->interviewer_id == ''
                || $interviewerClone->interviewer_id == null
                || $interviewerClone->main_dealer_code == ''
                || $interviewerClone->main_dealer_code == null
                || $interviewerClone->main_dealer_name == ''
                || $interviewerClone->main_dealer_name == null) return;

            $interviewerClone->achievement = filter_var($row[$i], FILTER_VALIDATE_INT) === false ? 0 : $row[$i];
            $interviewerClone->interview_date = $dates[$i];
            $interviewerClone->save();
            $i++;
        }
    }

    public function getMainDealersInterviewer($uuid, Request $request)
    {
        if (!(Auth::user()->role == User::ROLE_ADMIN || Auth::user()->role == User::ROLE_CLIENT)) {
            return response()->json('Unauthorized', 401);
        }

        $project = Project::where('uuid', $uuid)->first();

        $mainDealers = Interviewer::select('main_dealer_code', 'main_dealer_name')
                ->where('project_id', $project->id)
                ->where('main_dealer_name', '!=', '')
                ->distinct('main_dealer_code')
                ->get();

        return response()->json($mainDealers, 200);
    }

    public function getIdsInterviewer($uuid, Request $request)
    {
        if (!(Auth::user()->role == User::ROLE_ADMIN || Auth::user()->role == User::ROLE_CLIENT)) {
            return response()->json('Unauthorized', 401);
        }

        $project = Project::where('uuid', $uuid)->first();

        $idsInterviewer = Interviewer::select('interviewer_id')
            ->where('project_id', $project->id)
            ->where('main_dealer_name', '!=', '')
            ->where('main_dealer_code', $request->mainDealerCode)
            ->distinct('interviewer_id')
            ->get();

        return response()->json($idsInterviewer, 200);
    }

    public function getInterviewerChartDataByInterviewerId($uuid, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mainDealerCode' => 'required',
            'idInterviewer' => 'required',
            'filterDateFrom' => 'nullable|date',
            'filterDateTo' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json('Invalid Request', 422);
        }

        $project = Project::where('uuid', $uuid)->first();

        $mainDealerCode = $request->mainDealerCode;
        $idInterviewer = $request->idInterviewer;
        $interviewers = Interviewer::selectRaw('SUM(achievement) as achievement, interview_date')
            ->where('project_id', $project->id)
            ->where('main_dealer_code', $mainDealerCode)
            ->where('interviewer_id', $idInterviewer);

        if ($request->filterDateFrom != ''){
            $interviewers = $interviewers->where('interview_date', '>=', $request->filterDateFrom);
        }
        if ($request->filterDateTo != ''){
            $interviewers = $interviewers->where('interview_date', '<=', $request->filterDateTo);
        }

        $interviewers = $interviewers->groupBy('interview_date')->get();

        $response = (object) array(
            'achievement' => $interviewers->pluck('achievement'),
            'dates' => $interviewers->pluck('interview_date')
        );

        return response()->json($response);
    }

    public function getInterviewerChartData($uuid, Request $request) 
    {
        DB::enableQueryLog();
        $validator = Validator::make($request->all(), [
            'mainDealerCode' => 'required',
            'filterDateFrom' => 'nullable|date',
            'filterDateTo' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json('Invalid Request', 422);
        }

        $project = Project::where('uuid', $uuid)->first();

        $mainDealerCode = $request->mainDealerCode;
        $interviewers = Interviewer::selectRaw('SUM(achievement) as achievement, interviewer_id')
                ->where('project_id', $project->id)
                ->where('main_dealer_code', $mainDealerCode)
                ->where('interviewer_id', '!=', '');

        $filterDateFromData = Interviewer::select('interview_date')
            ->where('project_id', $project->id)
            ->where('main_dealer_code', $mainDealerCode)
            ->orderBy('interview_date', 'asc')
            ->first();

        if ($request->filterDateFrom != '') {
            $filterDateFrom = Carbon::parse($request->filterDateFrom);
            if ($filterDateFromData){
                $filterDateFrom = $filterDateFrom->greaterThan($filterDateFromData->interview_date) ?
                    $filterDateFrom->format('Y-m-d') : $filterDateFromData->interview_date;
            }else{
                $filterDateFrom = $filterDateFrom->format('Y-m-d');
            }
        }else{
            $filterDateFrom = $filterDateFromData ? $filterDateFromData->interview_date : null;
        }

        $filterDateToData = Interviewer::select('interview_date')
            ->where('project_id', $project->id)
            ->where('main_dealer_code', $mainDealerCode)
            ->orderBy('interview_date', 'desc')
            ->first();
        if ($request->filterDateTo != '') {
            $filterDateTo = Carbon::parse($request->filterDateTo);
            if ($filterDateToData) {
                $filterDateTo = $filterDateTo->lessThan($filterDateToData->interview_date) ?
                    $filterDateTo->format('Y-m-d') : $filterDateToData->interview_date;
            } else {
                $filterDateTo = $filterDateTo->format('Y-m-d');
            }
        } else {
            $filterDateTo = $filterDateToData ? $filterDateToData->interview_date : null;
        }

        if ($filterDateTo){
            $interviewers = $interviewers->where('interview_date', '<=', $filterDateTo);
        }
        if ($filterDateFrom){
            $interviewers = $interviewers->where('interview_date', '>=', $filterDateFrom);
        }

        $interviewers = $interviewers->groupBy('interviewer_id')
            ->get();

        $temp = array();
        $achievement = array();
        $labels = array();

        foreach ($interviewers as $item) {
            $temp[$item->interviewer_id] = $item->achievement;
        }

        arsort($temp);
        
        $i = 0;
        foreach ($temp as $key => $value) {
            $achievement[$i] = $value;
            $labels[$i] = $key;
            ++$i;
        }

        $response = (object) array(
            'achievement' => $achievement,
            'labels' => $labels,
            'threshold' => $this->getThresholdInterviewerChartData($project->id, $filterDateFrom, $filterDateTo)
        );

        return response()->json($response, 200);
    }

    private function getThresholdInterviewerChartData($projectId, $startDate, $endDate){
        $DEFAULT_MINIMUM_THRESHOLD_EACH_DAY = 2;

        if ($startDate === null && $endDate === null){
            return 0;
        }

        if ($startDate === $endDate) {
            $dates = array($startDate);
        }else{
            $dates = array();
            $start = Carbon::parse($startDate);
            $end = Carbon::parse($endDate);
            if ($start > $end){
                return 0;
            }
            $diff = $end->diffInDays($start);
            for ($i=0; $i <= $diff; $i++) {
                $dateName = $start->format('l');
                if ($dateName !== 'Sunday') {
                    $dates[] = $start->format('Y-m-d');
                }
                $start->addDay();
            }
        }
        $exclude = ExcludeInterviewDate::select('date')
            ->where('project_id', $projectId)->get();
        $exclude = $exclude->pluck('date')->toArray();

        $datesWithExclude = array_diff($dates, $exclude);

        return count($datesWithExclude) * $DEFAULT_MINIMUM_THRESHOLD_EACH_DAY;
    }
    public function importWeek(Request $request) 
    {
        if (Auth::user()->role != User::ROLE_ADMIN) return;

        $validator = Validator::make($request->all(), [
            'import_week' => 'required|file|mimes:xlsx'
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'File type must be xlsx'], 422);
        }

        if ($request->import_week) {
            $path = Storage::putFileAs('week-csi', $request->file('import_week'), $request->import_week->getClientOriginalName());
            $reader = ReaderFactory::create(Type::XLSX);
            $reader->open(storage_path('app/' . $path));

            // validate
            foreach ($reader->getSheetIterator() as $sheet) {
                if (!$this->validateWeekSheet($sheet))
                    return response()->json(['message' => 'File is not valid'], 422);
            }
            
            // import
            foreach ($reader->getSheetIterator() as $sheet) {
                $import = $this->importWeekData($request->project_id, $sheet);
                if(!is_bool($import)){
                    return response()->json(['message' => $import], 422);
                }
            }

            $reader->close();
            return response()->json(['message' => 'Success'], 200);
        }
    }

    private function validateWeekSheet(Sheet $sheet) 
    {
        $isValid = false;
        foreach ($sheet->getRowIterator() as $keyRow => $row) {
            if ($keyRow == 1 && $row[1] == 'MD' && $row[8] == 'TARGET PER WEEK') {
                $isValid = true;
                break;
            }
        }

        return $isValid;
    }

    private function importWeekData($projectId, Sheet $sheet, $deleteAndInsert = true) 
    {
        // if ($deleteAndInsert) {
        //     \App\ProgressWeek::where('project_id', $projectId)->delete();
        // }

        $dateCol = [];
        foreach ($sheet->getRowIterator() as $keyRow => $row) {
            if ($keyRow == 3) {
                for($x=9;$x<=100;$x=$x+2){
                    if($row[$x] != ''){
                        $dateCol[$x] = $row[$x];
                    }else{
                        break;
                    }
                }
            }

            if ($keyRow < 5) {
                continue;
            }

            if ($row[1] == null || $row[1] == '') {
                continue;
            }

            $data = $this->storeWeekData($projectId, $row, $dateCol);

            if(isset($data['error'])){
                return 'Error at row '.$keyRow;
                break;
            }
        }

        return true;
    }

    private function storeWeekData($projectId, $row, $dateCol) 
    {
        $x = 0;
        $week = [];

        foreach($dateCol as $key => $val){
            if(!isset($week[$key])){
                $x++;
                $week[$key] = $x;
            }

            $progress = \App\ProgressWeek::where('project_id', $projectId)
            ->where('main_dealer_code', $row[1])
            ->where('week', $week[$key])
            ->first();

            if($row[1] == null || $row[2] == null || $row[1] == '' || $row[2] == ''){
                return ['error' => true];
                break;
            }
            if(($row[$key] != null && $row[$key+1] != null) && (!is_int($row[$key]) || !is_int($row[$key+1]))){
                return ['error' => true];
                break;
            }

            if(!isset($progress->id)){
                $progressWeek = new \App\ProgressWeek;
                $progressWeek->project_id = $projectId;
                $progressWeek->main_dealer_code = $row[1];
                $progressWeek->main_dealer_name = $row[2];
                $progressWeek->week = $week[$key];
                $progressWeek->date = $val;
                $progressWeek->target = (int)$row[$key];
                $progressWeek->achievement = (int)$row[$key+1];
                $progressWeek->achievement_percent = (int)$row[$key] != 0?round(((int)$row[$key+1]/(int)$row[$key])*100):0;

                $progressWeek->save();
            }else{
                $progressData = [];
                $progressData['project_id'] = $projectId;
                $progressData['main_dealer_code'] = $row[1];
                $progressData['main_dealer_name'] = $row[2];
                $progressData['week'] = $week[$key];
                $progressData['date'] = $val;
                $progressData['target'] = (int)$row[$key];
                $progressData['achievement'] = (int)$row[$key+1];
                $progressData['achievement_percent'] = (int)$row[$key] != 0?round(((int)$row[$key+1]/(int)$row[$key])*100):0;

                $progressWeek = new \App\ProgressWeek;
                $progressWeek->where('id', $progress->id)->update($progressData);
            }
        }

    }

    public function getMainDealersWeek($uuid, Request $request)
    {
        if (!(Auth::user()->role == User::ROLE_ADMIN || Auth::user()->role == User::ROLE_CLIENT)) {
            return response()->json('Unauthorized', 401);
        }

        $project = Project::where('uuid', $uuid)->first();

        $mainDealers = \App\ProgressWeek::select('main_dealer_code', 'main_dealer_name')
                ->where('project_id', $project->id)
                ->where('main_dealer_name', '!=', '')
                ->distinct('main_dealer_code')
                ->get();

        return response()->json($mainDealers, 200);
    }

    public function getMainWeek($uuid, Request $request)
    {
        if (!(Auth::user()->role == User::ROLE_ADMIN || Auth::user()->role == User::ROLE_CLIENT)) {
            return response()->json('Unauthorized', 401);
        }

        $project = Project::where('uuid', $uuid)->first();

        $mainDealers = \App\ProgressWeek::select('week', 'date')
                ->where('project_id', $project->id)
                ->distinct('week','date')
                ->get();

        return response()->json($mainDealers, 200);
    }

    public function getWeekChartData($uuid, Request $request) 
    {
        $project = Project::where('uuid', $uuid)->first();

        $mainDealerCode = $request->mainDealerCode;
        $weekNumber = $request->week;

        $week = \App\ProgressWeek::select(DB::raw("SUM(target) as target"),DB::raw("SUM(achievement) as achievement"))
                ->where('project_id', $project->id);

        $totalWeek = \App\ProgressWeek::select(DB::raw("max(week) week"))
        ->where('achievement','>','0')->where('project_id', $project->id);

        if($mainDealerCode != '0' && $mainDealerCode != null){
            $week = $week->where('main_dealer_code', $mainDealerCode);
            $totalWeek = $totalWeek->where('main_dealer_code', $mainDealerCode);
        }
        
        if($weekNumber != '0'){
            $week = $week->where('week', $weekNumber);
        }

        $week = $week->get();
        $totalWeek = $totalWeek->first();

        $temp = array();
        $achievement = array();
        $labels = array();

        foreach ($week as $item) {
            $temp['target'] = $item->target;
            $temp['achievement'] = $item->achievement;
        }

        krsort($temp);
        
        $i = 0;
        foreach ($temp as $key => $value) {
            $achievement[$i] = $value;
            $labels[$i] = ucwords($key);
            ++$i;
        }

        $response = (object) array(
            'achievement' => $achievement,
            'labels' => $labels,
            'week' => isset($totalWeek->week)?$totalWeek->week:0
        );

        return response()->json($response, 200);
    }

    public function getWeekDeleteData($uuid, Request $request){
        $project = Project::where('uuid', $uuid)->first();
        $delete = \App\ProgressWeek::where('project_id', $project->id)->delete();

        if($delete){
            return response()->json(['message' => 'File has been deleted'], 200);
        }else{
            return response()->json(['message' => 'You haven\'t upload any file'], 401);
        }
    }
}