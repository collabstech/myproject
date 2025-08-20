<?php

namespace App\Http\Controllers;

use Validator;
use Illuminate\Http\Request;
use App\Company;
use App\Project;
use App\ProjectResultValue;
use App\Report;
use App\User;
use App\ProgressData;
use App\UserMainDealer;

use App\Http\Requests\StoreProjectRequest;
use App\Http\Controllers\FileController;

use DataTables;
use Auth;
use DB;
use Excel;

use Box\Spout\Reader\ReaderFactory;
use Box\Spout\Common\Type;
use Box\Spout\Reader\XLSX\Sheet;

use Chumper\Zipper\Zipper;

class ProjectController extends Controller
{
    private $companyModel;
    private $projectModel;
    private $valueModel;

    public function __construct(Project $project, ProjectResultValue $value, Company $company)
    {
        $this->projectModel = $project;
        $this->valueModel = $value;
        $this->companyModel = $company;
    }

    public function index()
    {
        if(Auth::user()->role == \App\User::ROLE_ADMIN) {
            return view('project.index');
        } else {
            return redirect()->route('home');
        }
    }

    public function listData(Request $request)
    {
        \DB::statement(\DB::raw('set @rownum='.(int) $request->start));
        $model = $this->projectModel->select([
            \DB::raw('@rownum  := @rownum  + 1 AS rownum'),
            'projects.id', 'projects.uuid', 'projects.name', 'objective', 'start_date', 'finish_date',
            'company_id',
            'projects.created_at', 'projects.updated_at',
        ])
        ->with('company')
        ;

        $datatables = DataTables::of($model);

        $datatables->editColumn('action', function ($model) {
            $reportAdmin = $model->report()->whereHas('user', function ($query) {
                $query->where('role', User::ROLE_ADMIN);
            })->first();
            if ($reportAdmin) {
                return '<a href="'.route('report.generate', ['project_id' => $model->uuid, 'report_id' => $reportAdmin->uuid]).'" class="btn btn-primary"><i class="fa fa-info-circle"></i> View Result</a>';
            } else {
                return '<a href="'.route('project.detail', ['uuid' => $model->uuid]).'" class="btn btn-primary"><i class="fa fa-info-circle"></i> Detail</a>';
            }
        });

        return $datatables->make(true);
    }

    public function detail($uuid, Request $request)
    {
        $project = $this->projectModel
        ->where('uuid', $uuid)
        ->first();

        $projectQuestion = $project->projectQuestion()->with('projectQuestionAnswer')->paginate(10);
        $projectResult = $project->projectResult()->get();

        $questionId = [];
        foreach ($projectResult as $key => $value) {
            $question = $value->projectResultValue()->whereRaw('`values` REGEXP "^(https?://|www\\.)[\.A-Za-z0-9\-]+\\.[a-zA-Z]{2,4}"');
            if ($question->count() > 0) {
                foreach ($question->get() as $key => $value) {
                    $questionId[$value->question_id] = $value->question_id;
                }
            }
        }
        $identityQuestion = $project->projectQuestion()->first();
        $attachmentQuestion = $project->projectQuestion()->whereIn('id', $questionId)->get();
        $selectedQuestion = $project->projectQuestion()->where('id', $request->question_id)->first();
        $descriptionQuestion = $project->projectQuestion()->where('id', '>', (int) $request->question_id)->orderBy('id', 'asc')->first();

        return view('project.detail', [
            'project' => $project,
            'projectQuestion' => $projectQuestion,
            'attachmentQuestion' => $attachmentQuestion,
            'identityQuestion' => $identityQuestion,
            'selectedQuestion' => $selectedQuestion,
            'descriptionQuestion' => $descriptionQuestion,
        ]);
    }

    public function attachmentData($uuid, Request $request)
    {
        $project = $this->projectModel
        ->where('uuid', $uuid)
        ->first();

        $identityQuestion = $project->projectQuestion()->first();
        $descriptionQuestion = $project->projectQuestion()->where('id', '>', $request->question_id)->orderBy('id', 'asc')->first();
        $attachmentQuestion = $project->projectQuestion()->whereIn('id', [$identityQuestion->id, $request->question_id, $descriptionQuestion->id])->pluck('id');
        $selectedQuestion = $project->projectQuestion()->where('id', $request->question_id)->first();

        $resultItem = $this->valueModel
        ->with('question')
        ->where('project_id', $project->id)
        ->whereIn('question_id', $attachmentQuestion)
        ->get()
        ;
        $res = [];
        foreach ($resultItem as $key => $value) {
            $res[$value->row][$value->question->alias] = $value->values;
        }

        $result = [];
        $indexRow = 0;
        foreach ($res as $key => $value) {
            $result[$indexRow] = $value;
            $indexRow++;
        }

        $collection = collect($result);

        $datatables = DataTables::of($collection);
        $datatables->editColumn('action', function ($model) use ($selectedQuestion) {
            if (isset($model[$selectedQuestion->alias])) {
                return '<a href="'.$model[$selectedQuestion->alias].'" class="btn btn-primary" target="_blank"><i class="fa fa-external-link"></i> View</a>';
            }
        });

        return $datatables->toJson();
    }

    public function listHistoryResult($projectId, Request $request)
    {
        $file = new FileController;
        $project = $this->projectModel->where('uuid', $projectId)->first();
        if (!$project) {
            $datatables = DataTables::collection([]);

            return $datatables->make(true);
        }

        \DB::statement(\DB::raw('set @rownum='.$request->start));
        $model = $project->projectResult()->select([
            \DB::raw('@rownum  := @rownum  + 1 AS rownum'),
            'result_date', 'result_code',
            'project_id',
            'created_at', 'updated_at',
        ])
        ;

        $datatables = DataTables::of($model);

        $datatables->editColumn('action', function ($model) use ($file) {
            $path = 'app'.DIRECTORY_SEPARATOR.'result'.DIRECTORY_SEPARATOR.$model->project->uuid.'_'.$model->result_code;
            if (!file_exists(storage_path($path))) {
                return $model->result_code;
            }
            return '<a href="'.route('download', ['path' => $path]).'" target="_blank">'.$model->result_code.'</a>';
        });

        return $datatables->make(true);
    }

    public function create()
    {
        $companies = $this->companyModel->orderBy('name', 'asc')->get();

        return view('project.form', ['companies' => $companies]);
    }

    public function edit($uuid)
    {
        $companies = $this->companyModel->orderBy('name', 'asc')->get();
        $project = $this->projectModel->where('uuid', $uuid)->first();
        $questionList = [];
        if ($project->projectQuestion) {
            $questionList = $project->projectQuestion;
        }

        return view('project.form', ['companies' => $companies, 'project' => $project, 'questionList' => $questionList]);
    }

    public function store(StoreProjectRequest $request)
    {
        ini_set('max_execution_time', 7200);
        DB::beginTransaction();
        try {
            $company = $this->companyModel->where('uuid', $request->company_id)->first();
            $project = $this->projectModel->firstOrNew(['uuid' => $request->uuid]);

            $project->uuid = $request->uuid != 'add' ? $request->uuid : \Uuid::generate();
            if ($request->uuid == 'add') {
                $project->name = 'Project name';
            }
            $project->type = $request->type;
            $project->company_id = $company->id;
            $project->save();

            $this->importProject($project, $request);
            $this->importResult($project, $request);
            if ($request->uuid != 'add') {
                $this->visibleType($project, $request);
                $this->visibleQuestion($project, $request);
            }

            DB::commit();
            $message = 'Project ['.$project->name.'] has been successfullly ';
            $message .= $request->uuid ? 'updated' : 'created';
            $message .= '.';
            return response()->json([
                'code' => 200,
                'message' => $message,
            ]);
        } catch (Exception $e) {
            DB::rollback();
            $message = 'Failed to ';
            $message .= $request->uuid ? 'update' : 'create';
            $message .= ' project ['.$project->name.'].';

            return response()->json([
                'code' => 400,
                'message' => $message,
            ]);
        }
    }

    private function importProject($project, Request $request)
    {
        if ($request->import_project) {
            $reader = ReaderFactory::create(Type::XLSX); // for XLSX files

            $reader->open($request->import_project);

            $dataProject = [];
            $dataQuestion = [];
            foreach ($reader->getSheetIterator() as $key => $sheet) {
                if ($key == 1) {
                    foreach ($sheet->getRowIterator() as $row) {
                        $dataProject[$row[0]] = $row[1];
                    }
                }
                if ($key == 2) {
                    foreach ($sheet->getRowIterator() as $row) {
                        $dataQuestion[$row[0]]['text'] = $row[1];
                        if (isset($row[2])) {
                            $dataQuestion[$row[0]]['alias'] = $row[2];
                        }
                    }
                }
            }
            $matchProjectField = [
                'project_code'          => 'code',
                'project_name'          => 'name',
                'start_date_timestamp'  => 'start_date',
                'end_date_timestamp'    => 'finish_date',
                'project_description'   => 'description',
                'methodology'           => 'methodology',
                'project_objective'     => 'objective',
                'target_respondent'     => 'respondent',
                'area_coverage'         => 'coverage',
                'project_timeline'      => 'timeline',
            ];
            foreach ($matchProjectField as $key => $value) {
                $project->{$value} = $dataProject[$key];
                if ($value == 'timeline') {
                    $project->{$value} = (int) $dataProject[$key];
                }
            }
            $project->save();

            $project->projectQuestionAnswer()->delete();
            foreach ($dataQuestion as $key => $value) {
                if ($value) {
                    if (!preg_match('/^Q([0-9]+)_/', $key)) {
                        // Search for question and save
                        $projectQuestion = $project->projectQuestion()->firstOrNew([
                            'code' => $key,
                        ]);
                        $projectQuestion->project_id = $project->id;
                        $projectQuestion->code = $key;
                        $projectQuestion->question = $value['text'];
                        if (isset($value['alias'])) {
                            $projectQuestion->question_alias = $value['alias'];
                        }
                        $projectQuestion->save();
                    } else {
                        // Search for answer and save
                        $projectAnswer = $project->projectQuestionAnswer()->create([
                            'question_id' => $projectQuestion->id,
                            'code' => $key,
                            'answer' => '',
                        ]);
                        $projectAnswer->project_id = $project->id;
                        $projectAnswer->question_id = $projectQuestion->id;
                        $projectAnswer->code = $key;
                        $projectAnswer->answer = $value['text'];
                        $projectAnswer->save();
                    }
                }
            }

            $reader->close();
            \Storage::putFileAs('project', $request->file('import_project'), $project->uuid.'_'.$request->import_project->getClientOriginalName());
        }
    }

    private function importResult($project, Request $request)
    {
        if ($request->import_result) {
            $projectResult = $project->projectResult()->create([
                'uuid' => \Uuid::generate(),
                'project_id' => $project->id,
                'result_date' => date('Y-m-d H:i:s'),
                'result_code' =>  $request->import_result->getClientOriginalName(),
            ]);

            $reader = ReaderFactory::create(Type::XLSX); // for XLSX files

            $reader->open($request->import_result);

            $extractRow = [];
            foreach ($reader->getSheetIterator() as $keySheet => $sheet) {
                if ($keySheet == 1) {
                    foreach ($sheet->getRowIterator() as $row) {
                        $extractRow[] = $row;
                    }
                }
            }
            $projectQuestion = $project->projectQuestion()->with('projectQuestionAnswer')->get();

            $data = [];
            $answer = [];
            $questionList = [];
            $answerList = [];
            $column = [];
            if (isset($extractRow[0])) {
                foreach ($extractRow[0] as $key => $value) {
                    $code = preg_replace('/-([0-9]+)/', '', $value);
                    $col = explode('-', $value);
                    // $questionList[$key] = $project->projectQuestion()->with('projectQuestionAnswer')->where('code', $code)->first();
                    $questionList[$key] = $projectQuestion->where('code', $code)->first();
                    if (isset($questionList[$key]->projectQuestionAnswer)) {
                        $answerList[$key][$code] = $questionList[$key]->projectQuestionAnswer;
                    }
                    if (isset($col[1])) {
                        $column[$key] = $col[1];
                    }
                }
            }
            foreach ($extractRow as $row => $val) {
                foreach ($val as $key => $value) {
                    if ($value && $row > 0) {
                        $question = $questionList[$key];
                        if ($question) {
                            $value = $value instanceof \DateTime ? $value->format('Y-m-d H:i:s') : $value;
                            // $answer = $question->projectQuestionAnswer()->where('code', $question->code.'_'.$value)->first();
                            $answer = $answerList[$key][$question->code]->where('code', $question->code.'_'.$value)->first();
                            $resultValue = $projectResult->projectResultValue()->create([
                                'row' => $row,
                                'project_id' => $project->id,
                                'result_id' => $projectResult->id,
                                'question_id' => $question->id,
                            ]);
                            $resultValue->fill([
                                'answer_id' => $answer ? $answer->id : null,
                                'answer_column' => isset($column[$key]) && $value ? $column[$key] : 1,
                                'values' => $answer ? $answer->answer : $value,
                            ]);
                            $resultValue->save();
                        }
                    }
                }
            }

            $reader->close();
            \Storage::putFileAs('result', $request->file('import_result'), $projectResult->uuid.'_'.$request->import_result->getClientOriginalName());
        }
    }

    private function visibleType($project, Request $request)
    {
        foreach (Report::typeVisible() as $key => $value) {
            $project->{$value} = isset($request->visibleType[$key]) ? 1 : 0;
        }
        $project->save();
    }

    private function visibleQuestion($project, Request $request)
    {
        $question = $project->projectQuestion()->get();
        if ($question) {
            foreach ($question as $key => $value) {
                $value->visibleTop = isset($request->visible[$value->id]['top']) ? 1 : 0;
                $value->visibleSide = isset($request->visible[$value->id]['side']) ? 1 : 0;
                $value->visibleValue = isset($request->visible[$value->id]['value']) ? 1 : 0;
                $value->visibleFilter = isset($request->visible[$value->id]['filter']) ? 1 : 0;
                $value->visibleSummary = isset($request->visible[$value->id]['summary']) ? 1 : 0;
                $value->save();
            }
        }
    }

    public function importProgress(Request $request, $deleteAndInsert = true)
    {
        if (Auth::user()->role != \App\User::ROLE_ADMIN) return;

        $validator = Validator::make($request->all(), [
            'import_progress' => 'required|file|mimes:xlsx'
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'File type must be xlsx'], 422);
        }

        if ($request->import_progress) {
            $path = \Storage::putFileAs('progress', $request->file('import_progress'), $request->import_progress->getClientOriginalName());
            $reader = ReaderFactory::create(Type::XLSX);
            $reader->open(storage_path('app/' . $path));

            if ($deleteAndInsert) {
                ProgressData::where('project_id', $request->project_id)->delete();
            }

            foreach ($reader->getSheetIterator() as $sheet) {
                if (!$this->validateProgressSheet($sheet)) {
                    continue;
                }
                $sheetName = $sheet->getName();

                foreach ($sheet->getRowIterator() as $keyRow => $row) {
                    if ($keyRow < 5 || filter_var($row[0], FILTER_VALIDATE_INT) === false) {
                        continue;
                    }

                    $this->storeProgressData($request->project_id, $row);
                }
            }

            $reader->close();
            return response()->json(['message' => 'Success'], 200);
        }
    }

    private function validateProgressSheet(Sheet $sheet)
    {
        $isValid = false;
        foreach ($sheet->getRowIterator() as $keyRow => $row) {
            if ($keyRow != 4) {
                continue;
            }
            if ($row[0] == 'No.' && $row[1] == 'Kode Main Dealer'
                && $row[2] == 'Main Dealer' && $row[3] == 'Distrik') {
                $isValid = true;
                break;
            }
        }
        return $isValid;
    }

    private function storeProgressData($projectId, $row)
    {
        if ($row[1] == '') return;

        $progressData = ProgressData::where('project_id', $projectId)
                ->where('main_dealer_code', $row[1])
                ->where('district', $row[3])
                ->where('dealer_code', $row[4])
                ->first();

        if (!$progressData) {
            $progressData = new ProgressData;
            $progressData->project_id = $projectId;
            $progressData->main_dealer_code = $row[1];
            $progressData->district = $row[3];
            $progressData->dealer_code = $row[4];
        }

        $progressData->main_dealer_name = $row[2];
        $progressData->dealer_name = $row[5];
        $progressData->h1_premium_target = filter_var($row[9], FILTER_VALIDATE_INT) === false ? 0 : $row[9];
        $progressData->h1_premium_achievement = filter_var($row[10], FILTER_VALIDATE_INT) === false ? 0 : $row[10];
        $progressData->h2_premium_target = filter_var($row[11], FILTER_VALIDATE_INT) === false ? 0 : $row[11];
        $progressData->h2_premium_achievement = filter_var($row[12], FILTER_VALIDATE_INT) === false ? 0 : $row[12];
        $progressData->h3_premium_target = filter_var($row[13], FILTER_VALIDATE_INT) === false ? 0 : $row[13];
        $progressData->h3_premium_achievement = filter_var($row[14], FILTER_VALIDATE_INT) === false ? 0 : $row[14];
        $progressData->total_target_premium = filter_var($row[15], FILTER_VALIDATE_INT) === false ? 0 : $row[15];
        $progressData->total_achievement_premium  = filter_var($row[16], FILTER_VALIDATE_INT) === false ? 0 : $row[16];
        $progressData->h1_regular_target = filter_var($row[17], FILTER_VALIDATE_INT) === false ? 0 : $row[17];
        $progressData->h1_regular_achievement = filter_var($row[18], FILTER_VALIDATE_INT) === false ? 0 : $row[18];
        $progressData->h2_regular_target = filter_var($row[19], FILTER_VALIDATE_INT) === false ? 0 : $row[19];
        $progressData->h2_regular_achievement = filter_var($row[20], FILTER_VALIDATE_INT) === false ? 0 : $row[20];
        $progressData->h3_regular_target = filter_var($row[21], FILTER_VALIDATE_INT) === false ? 0 : $row[21];
        $progressData->h3_regular_achievement = filter_var($row[22], FILTER_VALIDATE_INT) === false ? 0 : $row[22];
        $progressData->total_target_regular = filter_var($row[23], FILTER_VALIDATE_INT) === false ? 0 : $row[23];
        $progressData->total_achievement_regular = filter_var($row[24], FILTER_VALIDATE_INT) === false ? 0 : $row[24];
        $progressData->h1_total = filter_var($row[25], FILTER_VALIDATE_INT) === false ? 0 : $row[25];
        $progressData->h2_total = filter_var($row[26], FILTER_VALIDATE_INT) === false ? 0 : $row[26];
        $progressData->h3_total = filter_var($row[27], FILTER_VALIDATE_INT) === false ? 0 : $row[27];
        $progressData->total = filter_var($row[28], FILTER_VALIDATE_INT) === false ? 0 : $row[28];

        $progressData->save();
    }

    public function getMainDealers($uuid, Request $request)
    {
        $project = $this->projectModel->where('uuid', $uuid)->first();

        $brand = ($request->brand) ? $request->brand : '';

        if (Auth::user()->role == \App\User::ROLE_ADMIN || Auth::user()->role == \App\User::ROLE_CLIENT) {
            $mainDealers = ProgressData::select('main_dealer_code', 'main_dealer_name')
                    ->where('project_id', $project->id)
                    ->where(function($query) use ($brand) {
                        if ($brand != '') {
                            $query->where('brand', $brand);
                        }
                    })
                    ->distinct('main_dealer_code')
                    ->get();
        } else if (Auth::user()->role == \App\User::ROLE_MAIN_DEALER) {
            $mainDealers = ProgressData::select('progress_datas.main_dealer_code', 'main_dealer_name')
                    ->leftJoin('user_main_dealers', 'progress_datas.main_dealer_code', '=', 'user_main_dealers.main_dealer_code')
                    ->where('progress_datas.project_id', $project->id)
                    ->where('user_id', Auth::user()->id)
                    ->where(function($query) use ($brand) {
                        if ($brand != '') {
                            $query->where('progress_datas.brand', $brand);
                        }
                    })
                    ->distinct('main_dealer_code')
                    ->get();
        }

        return response()->json($mainDealers, 200);
    }

    public function getRegions($uuid, Request $request)
    {
        $project = $this->projectModel->where('uuid', $uuid)->first();

        $brand = ($request->brand) ? $request->brand : '';

        if (Auth::user()->role == \App\User::ROLE_ADMIN || Auth::user()->role == \App\User::ROLE_CLIENT) {
            $regions = ProgressData::select('district')
                    ->where('project_id', $project->id)
                    ->where(function($query) use ($brand) {
                        if ($brand != '') {
                            $query->where('brand', $brand);
                        }
                    })
                    ->where('main_dealer_code', $request->mainDealerCode)
                    ->distinct('district')
                    ->get();
        } else if (Auth::user()->role == \App\User::ROLE_MAIN_DEALER) {
            $regions = ProgressData::select('district')
                    ->leftJoin('user_main_dealers', 'progress_datas.main_dealer_code', '=', 'user_main_dealers.main_dealer_code')
                    ->where('progress_datas.project_id', $project->id)
                    ->where(function($query) use ($brand) {
                        if ($brand != '') {
                            $query->where('progress_datas.brand', $brand);
                        }
                    })
                    ->where('progress_datas.main_dealer_code', $request->mainDealerCode)
                    ->where('user_id', Auth::user()->id)
                    ->distinct('district')
                    ->get();
        }

        return response()->json($regions, 200);
    }

    public function getDealers($uuid, Request $request)
    {
        $project = $this->projectModel->where('uuid', $uuid)->first();

        if (Auth::user()->role == \App\User::ROLE_ADMIN || Auth::user()->role == \App\User::ROLE_CLIENT) {
            $dealers = ProgressData::select('dealer_code', 'dealer_name')
                    ->where('project_id', $project->id)
                    ->where('main_dealer_code', $request->mainDealerCode)
                    ->where('district', $request->region)
                    ->distinct('dealer_code')
                    ->get();
        } else if (Auth::user()->role == \App\User::ROLE_MAIN_DEALER) {
            $dealers = ProgressData::select('dealer_code', 'dealer_name')
                    ->leftJoin('user_main_dealers', 'progress_datas.main_dealer_code', '=', 'user_main_dealers.main_dealer_code')
                    ->where('progress_datas.project_id', $project->id)
                    ->where('progress_datas.main_dealer_code', $request->mainDealerCode)
                    ->where('district', $request->region)
                    ->where('user_id', Auth::user()->id)
                    ->distinct('dealer_code')
                    ->get();
        }

        return response()->json($dealers, 200);
    }

    public function getMainDealerAssignment($uuid) {
        $project = $this->projectModel->where('uuid', $uuid)->first();

        if (Auth::user()->role == \App\User::ROLE_ADMIN) {
            $mainDealers = ProgressData::select('main_dealer_code', 'main_dealer_name')
                    ->where('project_id', $project->id)
                    ->distinct('main_dealer_code')
                    ->get();

            $assignedUsers = UserMainDealer::select('main_dealer_code', 'user_id')
                    ->where('project_id', $project->id)
                    ->get();

            for ($i = 0; $i < sizeof($mainDealers); ++$i) {
                if ($mainDealers[$i]->main_dealer_name == '') {
                    unset($mainDealers[$i]);
                    continue;
                }

                $mainDealers[$i]->user_ids = array();
                $userIds = array();
                $index = 0;
                for ($j = 0; $j < sizeof($assignedUsers); ++$j) {
                    if ($assignedUsers[$j]->main_dealer_code == $mainDealers[$i]->main_dealer_code) {
                        $userIds[$index] = $assignedUsers[$j]->user_id;
                        ++$index;
                    }
                }
                $mainDealers[$i]->user_ids = $userIds;
            }

            $users = User::select('users.id', 'name')
                    ->leftJoin('user_projects', 'users.id', '=', 'user_projects.user_id')
                    ->where('user_projects.project_id', $project->id)
                    ->where('role', \App\User::ROLE_MAIN_DEALER)
                    ->distinct('user_projects.user_id')
                    ->get();

            return response()->json((object) array(
                    'users' => $users,
                    'main_dealers' => $mainDealers), 200);
        }
    }

    public function storeMainDealerAssignment($uuid, $mainDealerCode, Request $request)
    {
        $project = $this->projectModel->where('uuid', $uuid)->first();

        if (Auth::user()->role == \App\User::ROLE_ADMIN) {
            $userIds = json_decode($request->user_ids);
            for($i = 0; $i < sizeof($userIds); ++$i) {
                $userId = $userIds[$i];
                $projectId = $project->id;

                $userMainDealer = UserMainDealer::where('project_id', $projectId)
                        ->where('main_dealer_code', $mainDealerCode)
                        ->where('user_id', $userId)
                        ->first();

                if($userId == 0 && $userMainDealer != null) {
                    $userMainDealer->delete();
                    continue;
                }

                if (!$userMainDealer) {
                    $userMainDealer = new UserMainDealer;
                    $userMainDealer->project_id = $projectId;
                    $userMainDealer->main_dealer_code = $mainDealerCode;
                }

                $userMainDealer->user_id = $userId;
                $userMainDealer->save();
            }

            return response()->json(['message' => 'Success'], 200);
        }
    }

    public function getProgressChartData($uuid, Request $request)
    {
        $project = $this->projectModel->where('uuid', $uuid)->first();

        $progressType = 'CSL';
        $brand = '0';
        if (array_key_exists('brand', $request->all())) {
            $brand = $request->brand;
            $progressType = 'CSI';
        }
        $mainDealerCode = $request->mainDealerCode;
        $region = $request->region;
        $dealerCode = ($request->dealerCode) ? $request->dealerCode : '0';

        $query = ProgressData::query();
        $query->selectRaw('
                    sum(h1_premium_target) as h1_premium_target,
                    sum(h1_premium_achievement) as h1_premium_achievement,
                    sum(h2_premium_target) as h2_premium_target,
                    sum(h2_premium_achievement) as h2_premium_achievement,
                    sum(h3_premium_target) as h3_premium_target,
                    sum(h3_premium_achievement) as h3_premium_achievement,
                    sum(total_target_premium) as total_target_premium,
                    sum(total_achievement_premium) as total_achievement_premium,
                    sum(h1_regular_target) as h1_regular_target,
                    sum(h1_regular_achievement) as h1_regular_achievement,
                    sum(h2_regular_target) as h2_regular_target,
                    sum(h2_regular_achievement) as h2_regular_achievement,
                    sum(h3_regular_target) as h3_regular_target,
                    sum(h3_regular_achievement) as h3_regular_achievement,
                    sum(total_target_regular) as total_target_regular,
                    sum(total_achievement_regular) as total_achievement_regular,
                    sum(h1_total) as h1_total,
                    sum(h2_total) as h2_total,
                    sum(h3_total) as h3_total,
                    sum(total) as total
                ');
        $query->where('project_id', $project->id);

        if ($brand != '0') {
            $query->where('brand', $brand);
        }
        if ($mainDealerCode != '0') {
            $query->where('main_dealer_code', $mainDealerCode);
        }
        if ($region != '0') {
            $query->where('district', $region);
        }
        if ($dealerCode != '0') {
            $query->where('dealer_code', $dealerCode);
        }

        if (Auth::user()->role == \App\User::ROLE_MAIN_DEALER && $mainDealerCode == '0') {
            $mainDealerCodes = UserMainDealer::select('main_dealer_code')
                    ->where('user_id', Auth::user()->id)
                    ->get();
            for ($i = 0; $i < sizeof($mainDealerCodes); ++$i) {
                $query->orWhere('main_dealer_code', $mainDealerCodes[$i]->main_dealer_code);
            }
        }

        $progressDatas = $query->get();

        $premiumTarget = array(
                $progressDatas[0]->h1_premium_target,
                $progressDatas[0]->h2_premium_target,
                $progressDatas[0]->h3_premium_target
            );

        $regularTarget = array(
                $progressDatas[0]->h1_regular_target,
                $progressDatas[0]->h2_regular_target,
                $progressDatas[0]->h3_regular_target
            );

        if ($progressType == 'CSI') {
            $premiumTarget = array(
                $progressDatas[0]->h1_total,
                $progressDatas[0]->h2_total,
                $progressDatas[0]->h3_total
            );

            $regularTarget = $premiumTarget;
        }

        $premiumAchievement = array(
                    $progressDatas[0]->h1_premium_achievement,
                    $progressDatas[0]->h2_premium_achievement,
                    $progressDatas[0]->h3_premium_achievement
                );

        $regularAchievement = array(
                    $progressDatas[0]->h1_regular_achievement,
                    $progressDatas[0]->h2_regular_achievement,
                    $progressDatas[0]->h3_regular_achievement
                );

        $h1Achievement = $progressDatas[0]->h1_premium_achievement + $progressDatas[0]->h1_regular_achievement;
        $h2Achievement = $progressDatas[0]->h2_premium_achievement + $progressDatas[0]->h2_regular_achievement;
        $h3Achievement = $progressDatas[0]->h3_premium_achievement + $progressDatas[0]->h3_regular_achievement;

        $response = (object) array(
                    'progress_type' => $progressType,
                    'h1_total' => $progressDatas[0]->h1_total,
                    'h2_total' => $progressDatas[0]->h2_total,
                    'h3_total' => $progressDatas[0]->h3_total,
                    'total' => $progressDatas[0]->total,
                    'h1_achievement' => $h1Achievement,
                    'h2_achievement' => $h2Achievement,
                    'h3_achievement' => $h3Achievement,
                    'total_achievement' => $h1Achievement + $h2Achievement + $h3Achievement,
                    'premium_target' => '',
                    'premium_achievement' => '',
                    'regular_target' => '',
                    'regular_achievement' => '',
                );

        $response->premium_target = $premiumTarget;
        $response->premium_achievement = $premiumAchievement;
        $response->regular_target = $regularTarget;
        $response->regular_achievement = $regularAchievement;

        return response()->json($response, 200);
    }

    public function uploadProgressTotal($uuid, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'upload_progress' => 'required|file|mimes:xlsx'
        ]);

        if ($validator->fails()) {
            return response()->json('File type must be xlsx', 422);
        }

        if (Auth::user()->role != \App\User::ROLE_ADMIN) return;

        $xlsExtension = ['xlsx', 'xlsm', 'xlsb'];
        $extension = '';
        foreach ($xlsExtension as $ext) {
            if (\Storage::disk('local')->exists('progress/' . $uuid . '.' . $ext)) {
                $extension = $ext;
                break;
            }
        }

        if ($request->upload_progress) {
            if ($extension != '') {
                \Storage::delete('progress/' . $uuid . '.' . $extension);
            }
            $path = \Storage::putFileAs('progress', $request->file('upload_progress'), $uuid . '.' . $request->file('upload_progress')->getClientOriginalExtension());
            return response()->json('File has been uploaded', 200);
        }
    }

    public function deleteProgressTotal($uuid)
    {
        if (Auth::user()->role != \App\User::ROLE_ADMIN) return;

        $xlsExtension = ['xlsx', 'xlsm', 'xlsb'];
        $extension = '';
        foreach ($xlsExtension as $ext) {
            if (\Storage::disk('local')->exists('progress/' . $uuid . '.' . $ext)) {
                $extension = $ext;
                break;
            }
        }
        if ($extension == '') {
            return response()->json('You haven\'t upload any file', 404);
        }

        \Storage::delete('progress/' . $uuid . '.' . $extension);
        return response()->json('File has been deleted', 200);
    }

    public function downloadProgressTotal($uuid)
    {
        if (Auth::user()->role != \App\User::ROLE_ADMIN
                && Auth::user()->role != \App\User::ROLE_CLIENT) return;

        $xlsExtension = ['xlsx', 'xlsm', 'xlsb'];
        $extension = '';
        foreach ($xlsExtension as $ext) {
            if (\Storage::disk('local')->exists('progress/' . $uuid . '.' . $ext)) {
                $extension = $ext;
                break;
            }
        }
        if ($extension == '') {
            echo 'File doesn\'t exist';
            return;
        }

        return response()->download(storage_path('app/progress/' . $uuid . '.' . $extension), 'File Progress.' . $extension);
    }

    public function getTotalChartData($uuid, Request $request)
    {
        $mainDealerCode = $request->mainDealerCode;
        $region = $request->region;
        $surveyType = $request->surveyType;
        $respondentType = $request->respondentType;
        $type = $request->type;

        if ($type == 'total-district' && $mainDealerCode == '0') {
            return response()->json('Please select Main Dealer', 400);
        }

        if ($type == 'total-dealer' && ($mainDealerCode == '0' || $region == '0')) {
            return response()->json('Please select Main Dealer or District', 400);
        }

        $project = $this->projectModel->where('uuid', $uuid)->first();

        $query = ProgressData::query();
        $query->where('project_id', $project->id);

        if ($mainDealerCode != '0') {
            $query->where('main_dealer_code', $mainDealerCode);
        }
        if ($region != '0') {
            $query->where('district', $region);
        }

        $select = '';
        if($mainDealerCode == '0') {
            $query->groupBy('main_dealer_name');
            $select = 'main_dealer_name as label,';
        }
        else if($region == '0') {
            $query->groupBy('district');
            $select = 'district as label,';
        }
        else {
            $query->groupBy('dealer_name');
            $select = 'dealer_name as label,';
        }

        $query->selectRaw($select . '
                    sum(h1_premium_target) as h1_premium_target,
                    sum(h1_premium_achievement) as h1_premium_achievement,
                    sum(h2_premium_target) as h2_premium_target,
                    sum(h2_premium_achievement) as h2_premium_achievement,
                    sum(h3_premium_target) as h3_premium_target,
                    sum(h3_premium_achievement) as h3_premium_achievement,
                    sum(total_target_premium) as total_target_premium,
                    sum(total_achievement_premium) as total_achievement_premium,
                    sum(h1_regular_target) as h1_regular_target,
                    sum(h1_regular_achievement) as h1_regular_achievement,
                    sum(h2_regular_target) as h2_regular_target,
                    sum(h2_regular_achievement) as h2_regular_achievement,
                    sum(h3_regular_target) as h3_regular_target,
                    sum(h3_regular_achievement) as h3_regular_achievement,
                    sum(total_target_regular) as total_target_regular,
                    sum(total_achievement_regular) as total_achievement_regular,
                    sum(h1_total) as h1_total,
                    sum(h2_total) as h2_total,
                    sum(h3_total) as h3_total,
                    sum(total) as total
                ');

        if (Auth::user()->role == \App\User::ROLE_MAIN_DEALER && $mainDealerCode == '0') {
            $mainDealerCodes = UserMainDealer::select('main_dealer_code')
                    ->where('user_id', Auth::user()->id)
                    ->get();
            for ($i = 0; $i < sizeof($mainDealerCodes); ++$i) {
                $query->orWhere('main_dealer_code', $mainDealerCodes[$i]->main_dealer_code);
            }
        }

        $progressDatas = $query->get();
        $achievementDatas = array();
        $targetDatas = array();
        $labels = array();

        $surveyType = $request->surveyType;
        $respondentType = $request->respondentType;
        $totalAchievement = 0;
        $index = 0;
        for ($i = 0; $i < sizeof($progressDatas); ++$i) {
            if ($progressDatas[$i]->label == null || $progressDatas[$i]->label == '') continue;

            $achievement = 0;
            $target = 0;
            if ($respondentType == '1') {
                if ($surveyType == '1') {
                    $achievement = $progressDatas[$i]->h1_premium_achievement;
                    $target = $progressDatas[$i]->h1_premium_target;
                } else if ($surveyType == '2') {
                    $achievement = $progressDatas[$i]->h2_premium_achievement;
                    $target = $progressDatas[$i]->h2_premium_target;
                } else if ($surveyType == '3') {
                    $achievement = $progressDatas[$i]->h3_premium_achievement;
                    $target = $progressDatas[$i]->h3_premium_target;
                } else {
                    $achievement = $progressDatas[$i]->h1_premium_achievement
                            + $progressDatas[$i]->h2_premium_achievement
                            + $progressDatas[$i]->h3_premium_achievement;
                    $target = $progressDatas[$i]->h1_premium_target
                            + $progressDatas[$i]->h2_premium_target
                            + $progressDatas[$i]->h3_premium_target;
                }
            } else if ($respondentType == '2') {
                if ($surveyType == '1') {
                    $achievement = $progressDatas[$i]->h1_regular_achievement;
                    $target = $progressDatas[$i]->h1_regular_target;
                } else if ($surveyType == '2') {
                    $achievement = $progressDatas[$i]->h2_regular_achievement;
                    $target = $progressDatas[$i]->h2_regular_target;
                } else if ($surveyType == '3') {
                    $achievement = $progressDatas[$i]->h3_regular_achievement;
                    $target = $progressDatas[$i]->h3_regular_target;
                } else {
                    $achievement = $progressDatas[$i]->h1_regular_achievement
                            + $progressDatas[$i]->h2_regular_achievement
                            + $progressDatas[$i]->h3_regular_achievement;
                    $target = $progressDatas[$i]->h1_regular_target
                            + $progressDatas[$i]->h2_regular_target
                            + $progressDatas[$i]->h3_regular_target;
                }
            } else {
                if ($surveyType == '1') {
                    $achievement = $progressDatas[$i]->h1_regular_achievement + $progressDatas[$i]->h1_premium_achievement;
                    $target = $progressDatas[$i]->h1_regular_target + $progressDatas[$i]->h1_premium_target;
                } else if ($surveyType == '2') {
                    $achievement = $progressDatas[$i]->h2_regular_achievement + $progressDatas[$i]->h2_premium_achievement;
                    $target = $progressDatas[$i]->h2_regular_target + $progressDatas[$i]->h2_premium_target;
                } else if ($surveyType == '3') {
                    $achievement = $progressDatas[$i]->h3_regular_achievement + $progressDatas[$i]->h3_premium_achievement;
                    $target = $progressDatas[$i]->h3_regular_target + $progressDatas[$i]->h3_premium_target;
                } else {
                    $achievement = $progressDatas[$i]->total_achievement_regular
                            + $progressDatas[$i]->total_achievement_premium;
                    $target = $progressDatas[$i]->total_target_regular
                            + $progressDatas[$i]->total_target_premium;
                }
            }
            $totalAchievement += $achievement;
            $achievementDatas[$index] = $achievement;
            $targetDatas[$index] = $target;
            $labels[$index] = $progressDatas[$i]->label;
            ++$index;
        }

        $response = (object) array(
                    'type' => $type,
                    'total_achievement' => $totalAchievement,
                    'target' => $targetDatas,
                    'achievement' => $achievementDatas,
                    'label' => $labels
                );

        $sortedLabels = json_decode($request->sortedLabels);
        if (!array_key_exists('sortedLabels', $request->all()) ||
                (sizeof($sortedLabels) != sizeof($response->label))) {
            $response = $this->sortProgressTotalData($response);
        } else {
            $response = $this->sortProgressTotalDataByParam($sortedLabels, $response);
        }

        return response()->json($response, 200);
    }

    public function getCSITotalChartData($uuid, Request $request)
    {
        if (Auth::user()->role == \App\User::ROLE_MAIN_DEALER) {
            return response()->json('Unauthorized', 401);
        }

        $brand = $request->brand;
        $mainDealerCode = $request->mainDealerCode;
        $surveyType = $request->surveyType;
        $respondentType = $request->respondentType;
        $type = $request->type;

        if ($type == 'total-main-dealer' && $brand == '0') {
            return response()->json('Please select Brand', 400);
        }

        if ($type == 'total-district' && ($brand == '0' || $mainDealerCode == '0')) {
            return response()->json('Please select Brand or Main Dealer', 400);
        }

        $project = $this->projectModel->where('uuid', $uuid)->first();

        $query = ProgressData::query();
        $query->where('project_id', $project->id);

        if ($brand != '0') {
            $query->where('brand', $brand);
        }
        if ($mainDealerCode != '0') {
            $query->where('main_dealer_code', $mainDealerCode);
        }

        $select = '';
        if ($brand == '0') {
            $query->groupBy('brand');
            $select = 'brand as label,';
        }
        else if ($mainDealerCode == '0') {
            $query->groupBy('main_dealer_name');
            $select = 'main_dealer_name as label,';
        }
        else {
            $query->groupBy('district');
            $select = 'district as label,';
        }

        $query->selectRaw($select . '
                    sum(h1_premium_achievement) as h1_premium_achievement,
                    sum(h2_premium_achievement) as h2_premium_achievement,
                    sum(h3_premium_achievement) as h3_premium_achievement,
                    sum(h1_regular_achievement) as h1_regular_achievement,
                    sum(h2_regular_achievement) as h2_regular_achievement,
                    sum(h3_regular_achievement) as h3_regular_achievement,
                    sum(h1_total) as h1_total,
                    sum(h2_total) as h2_total,
                    sum(h3_total) as h3_total,
                    sum(total) as total,
                    sum(h1_total_achievement) as h1_total_achievement,
                    sum(h2_total_achievement) as h2_total_achievement,
                    sum(h3_total_achievement) as h3_total_achievement,
                    sum(total_achievement) as total_achievement
                ');

        $progressDatas = $query->get();
        $achievementDatas = array();
        $targetDatas = array();
        $labels = array();

        $surveyType = $request->surveyType;
        $respondentType = $request->respondentType;
        $totalAchievement = 0;
        $index = 0;
        for ($i = 0; $i < sizeof($progressDatas); ++$i) {
            if ($progressDatas[$i]->label == null || $progressDatas[$i]->label == '') continue;

            $achievement = 0;
            $target = 0;
            if ($respondentType == '1') {
                if ($surveyType == '1') {
                    $achievement = $progressDatas[$i]->h1_premium_achievement;
                    $target = $progressDatas[$i]->h1_total;
                } else if ($surveyType == '2') {
                    $achievement = $progressDatas[$i]->h2_premium_achievement;
                    $target = $progressDatas[$i]->h2_total;
                } else if ($surveyType == '3') {
                    $achievement = $progressDatas[$i]->h3_premium_achievement;
                    $target = $progressDatas[$i]->h3_total;
                } else {
                    $achievement = $progressDatas[$i]->h1_premium_achievement
                            + $progressDatas[$i]->h2_premium_achievement
                            + $progressDatas[$i]->h3_premium_achievement;
                    $target = $progressDatas[$i]->h1_total
                            + $progressDatas[$i]->h2_total
                            + $progressDatas[$i]->h3_total;
                }
            } else if ($respondentType == '2') {
                if ($surveyType == '1') {
                    $achievement = $progressDatas[$i]->h1_regular_achievement;
                    $target = $progressDatas[$i]->h1_total;
                } else if ($surveyType == '2') {
                    $achievement = $progressDatas[$i]->h2_regular_achievement;
                    $target = $progressDatas[$i]->h2_total;
                } else if ($surveyType == '3') {
                    $achievement = $progressDatas[$i]->h3_regular_achievement;
                    $target = $progressDatas[$i]->h3_total;
                } else {
                    $achievement = $progressDatas[$i]->h1_regular_achievement
                            + $progressDatas[$i]->h2_regular_achievement
                            + $progressDatas[$i]->h3_regular_achievement;
                    $target = $progressDatas[$i]->h1_total
                            + $progressDatas[$i]->h2_total
                            + $progressDatas[$i]->h3_total;
                }
            } else {
                if ($surveyType == '1') {
                    $achievement = $progressDatas[$i]->h1_total_achievement;
                    $target = $progressDatas[$i]->h1_total;
                } else if ($surveyType == '2') {
                    $achievement = $progressDatas[$i]->h2_total_achievement;
                    $target = $progressDatas[$i]->h2_total;
                } else if ($surveyType == '3') {
                    $achievement = $progressDatas[$i]->h3_total_achievement;
                    $target = $progressDatas[$i]->h3_total;
                } else {
                    $achievement = $progressDatas[$i]->total_achievement;
                    $target = $progressDatas[$i]->total;
                }
            }
            $totalAchievement += $achievement;
            $achievementDatas[$index] = $achievement;
            $targetDatas[$index] = $target;
            $labels[$index] = $progressDatas[$i]->label;
            ++$index;
        }

        $response = (object) array(
                    'type' => $type,
                    'total_achievement' => $totalAchievement,
                    'target' => $targetDatas,
                    'achievement' => $achievementDatas,
                    'label' => $labels
                );

        $sortedLabels = json_decode($request->sortedLabels);
        if (!array_key_exists('sortedLabels', $request->all()) ||
                (sizeof($sortedLabels) != sizeof($response->label))) {
            $response = $this->sortProgressTotalData($response);
        } else {
            $response = $this->sortProgressTotalDataByParam($sortedLabels, $response);
        }

        return response()->json($response, 200);
    }

    private function sortProgressTotalData($data)
    {
        if (sizeof($data->target) <= 0) {
            return $data;
        }

        $sortedData = (object) array(
                    'type' => $data->type,
                    'total_achievement' => $data->total_achievement,
                    'target' => null,
                    'achievement' => null,
                    'label' => null
                );

        $sortedData->target = array();
        $sortedData->achievement = array();
        $sortedData->label = array();

        $length = sizeof($data->target);
        $highest = $data->target[0];
        for ($i = 0; $i < $length; ++$i) {
            $highest = 0;
            $highestKey = 0;
            foreach ($data->target as $key => $val) {
                if ($highest <= $val) {
                    $highestKey = $key;
                    $highest = $val;
                }
            }
            $sortedData->target[$i] = $data->target[$highestKey];
            $sortedData->achievement[$i] = $data->achievement[$highestKey];
            $sortedData->label[$i] = $data->label[$highestKey];
            unset($data->target[$highestKey]);
            unset($data->achievement[$highestKey]);
            unset($data->label[$highestKey]);
        }

        return $sortedData;
    }

    private function sortProgressTotalDataByParam($sortedLabels, $data)
    {
        if (sizeof($data->label) <= 0 || sizeof($data->label) != sizeof($sortedLabels)) {
            return $data;
        }

        $sortedData = (object) array(
                    'type' => $data->type,
                    'total_achievement' => $data->total_achievement,
                    'target' => null,
                    'achievement' => null,
                    'label' => null
                );

        $sortedData->target = array();
        $sortedData->achievement = array();
        $sortedData->label = array();

        for ($i = 0; $i < sizeof($sortedLabels); ++$i) {
            foreach ($data->label as $key => $val) {
                if ($sortedLabels[$i] == $val) {
                    $sortedData->target[$i] = $data->target[$key];
                    $sortedData->achievement[$i] = $data->achievement[$key];
                    $sortedData->label[$i] = $data->label[$key];
                    unset($data->target[$key]);
                    unset($data->achievement[$key]);
                    unset($data->label[$key]);
                    break;
                }
            }
        }

        return $sortedData;
    }

    public function importCSIProgress(Request $request, $deleteAndInsert = true)
    {
        if (Auth::user()->role != \App\User::ROLE_ADMIN) return;

        $validator = Validator::make($request->all(), [
            'import_progress' => 'required|file|mimes:xlsx'
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'File type must be xlsx'], 422);
        }

        if ($request->import_progress) {
            $path = \Storage::putFileAs('progress-csi', $request->file('import_progress'), $request->import_progress->getClientOriginalName());
            $reader = ReaderFactory::create(Type::XLSX);
            $reader->open(storage_path('app/' . $path));

            if ($deleteAndInsert) {
                ProgressData::where('project_id', $request->project_id)->delete();
            }

            foreach ($reader->getSheetIterator() as $sheet) {
                if (!$this->validateCSIProgressSheet($sheet)) {
                    continue;
                }
                $sheetName = $sheet->getName();

                $brands = array();
                foreach ($sheet->getRowIterator() as $keyRow => $row) {
                    if ($keyRow < 1 || $keyRow == 2) {
                        continue;
                    }

                    if ($keyRow == 1) {
                        $brands[0] = $row[4];
                        $brands[1] = $row[19];
                        $brands[2] = $row[34];
                        continue;
                    }

                    if ($row[1] == null || $row[1] == '') {
                        continue;
                    }

                    $this->storeCSIProgressData($request->project_id, $brands, $row);
                }
            }

            $reader->close();
            return response()->json(['message' => 'Success'], 200);
        }
    }

    private function validateCSIProgressSheet(Sheet $sheet)
    {
        $isValid = false;
        foreach ($sheet->getRowIterator() as $keyRow => $row) {
            if ($keyRow != 2) {
                continue;
            }
            if ($row[1] == 'MD Code' && $row[2] == 'MD'
                && $row[3] == 'Distrik') {
                $isValid = true;
                break;
            }
        }
        return $isValid;
    }

    private function storeCSIProgressData($projectId, $brands, $row)
    {
        // if ($row[1] == '') return;

        foreach ($brands as $key => $brand) {
            $progressData = ProgressData::where('project_id', $projectId)
                    ->where('brand', $brand)
                    ->where('main_dealer_code', $row[1])
                    ->where('district', $row[3])
                    ->first();

            if (!$progressData) {
                $progressData = new ProgressData;
                $progressData->project_id = $projectId;
                $progressData->brand = $brand;
                $progressData->main_dealer_code = $row[1];
                $progressData->district = $row[3];
                $progressData->dealer_code = '0';
                $progressData->dealer_name = '';
            }

            $progressData->main_dealer_name = $row[2];
            $progressData->h1_total = filter_var($row[15 * $key + 4], FILTER_VALIDATE_INT) === false ? 0 : $row[15 * $key + 4];
            $progressData->h1_premium_achievement = filter_var($row[15 * $key + 5], FILTER_VALIDATE_INT) === false ? 0 : $row[15 * $key + 5];
            $progressData->h1_regular_achievement = filter_var($row[15 * $key + 6], FILTER_VALIDATE_INT) === false ? 0 : $row[15 * $key + 6];
            $progressData->h1_total_achievement = filter_var($row[15 * $key + 7], FILTER_VALIDATE_INT) === false ? 0 : $row[15 * $key + 7];
            $progressData->h2_total = filter_var($row[15 * $key + 8], FILTER_VALIDATE_INT) === false ? 0 : $row[15 * $key + 8];
            $progressData->h2_premium_achievement = filter_var($row[15 * $key + 9], FILTER_VALIDATE_INT) === false ? 0 : $row[15 * $key + 9];
            $progressData->h2_regular_achievement = filter_var($row[15 * $key + 10], FILTER_VALIDATE_INT) === false ? 0 : $row[15 * $key + 10];
            $progressData->h2_total_achievement = filter_var($row[15 * $key + 11], FILTER_VALIDATE_INT) === false ? 0 : $row[15 * $key + 11];
            $progressData->h3_total = filter_var($row[15 * $key + 12], FILTER_VALIDATE_INT) === false ? 0 : $row[15 * $key + 12];
            $progressData->h3_premium_achievement = filter_var($row[15 * $key + 13], FILTER_VALIDATE_INT) === false ? 0 : $row[15 * $key + 13];
            $progressData->h3_regular_achievement = filter_var($row[15 * $key + 14], FILTER_VALIDATE_INT) === false ? 0 : $row[15 * $key + 14];
            $progressData->h3_total_achievement = filter_var($row[15 * $key + 15], FILTER_VALIDATE_INT) === false ? 0 : $row[15 * $key + 15];
            $progressData->total = filter_var($row[15 * $key + 16], FILTER_VALIDATE_INT) === false ? 0 : $row[15 * $key + 16];
            $progressData->total_achievement = filter_var($row[15 * $key + 17], FILTER_VALIDATE_INT) === false ? 0 : $row[15 * $key + 17];

            if ($progressData->h1_total == 0
                && $progressData->h1_premium_achievement == 0
                && $progressData->h1_regular_achievement == 0
                && $progressData->h1_total_achievement == 0
                && $progressData->h2_total == 0
                && $progressData->h2_premium_achievement == 0
                && $progressData->h2_regular_achievement == 0
                && $progressData->h2_total_achievement == 0
                && $progressData->h3_total == 0
                && $progressData->h3_premium_achievement == 0
                && $progressData->h3_regular_achievement == 0
                && $progressData->h3_total_achievement == 0
                && $progressData->total == 0
                && $progressData->total_achievement == 0
            ) {
                continue;
            }
            $progressData->save();
        }
    }

    public function getBrands($uuid)
    {
        $project = $this->projectModel->where('uuid', $uuid)->first();

        if (!(Auth::user()->role == \App\User::ROLE_ADMIN || Auth::user()->role == \App\User::ROLE_CLIENT)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $brands = ProgressData::select('brand')
            ->where('project_id', $project->id)
            ->distinct('brand')
            ->get();

        return response()->json($brands, 200);
    }

    private function isFolderGranted($mainDealerCodes, $path)
    {
        $path = str_replace('//', '/', $path);
        if ($path[0] == '/') {
            $path = substr($path, 1);
        }
        $dirs = explode('/', $path);

        if (in_array($dirs[0], $mainDealerCodes)) {
            return true;
        }

        return false;
    }

    public function getAllFolders($uuid)
    {
        $client = new \GuzzleHttp\Client(['headers' => $this->setHeader()]);
        $response = $client->request('GET', env('IONBOARD_API_URL').'/file/'.$uuid.'/fm/folders');
        $response = $response->getBody()->getContents();
        $response = json_decode($response, true);

        return response()->json($response, 200);
    }

    public function getAllFiles($uuid, Request $request)
    {
        $client = new \GuzzleHttp\Client(['headers' => $this->setHeader()]);
        $response = $client->request('GET', env('IONBOARD_API_URL').'/file/'.$uuid.'/fm/files?path='.$request->input('path'));
        $response = $response->getBody()->getContents();
        $response = json_decode($response, true);
        
        return response()->json($response, 200);
    }

    public function fileManagerNewFolder($uuid, Request $request) 
    {
        $client = new \GuzzleHttp\Client(['headers' => $this->setHeader()]);
        $response = $client->request('POST', env('IONBOARD_API_URL').'/file/'.$uuid.'/fm/folder', ['form_params' => $request->all()]);
        $response = $response->getBody()->getContents();

        return $response;
    }

    public function getFileInfo($uuid, Request $request) 
    {
        $client = new \GuzzleHttp\Client(['headers' => $this->setHeader()]);
        $response = $client->request('GET', env('IONBOARD_API_URL').'/file/'.$uuid.'/fm/file?filepath='.$request->filepath);
        $response = $response->getBody()->getContents();
        $response = json_decode($response, true);
        
        return response()->json($response, 200);
    }

    public function fileManagerUpload($uuid, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'files' => 'required',
            'files.*' => 'mimes:doc,docx,ppt,pptx,xls,xlsx,pdf,zip,jpg,jpeg,gif,png,mpga,wav,weba,video/3gp,mp4,3g2,mpeg,avi,mpeg'
        ]);

        if ($validator->fails()) {
            return response()->json('File type not allowed', 422);
        }

        if (Auth::user()->role != \App\User::ROLE_ADMIN) return;

        $result = (object) array(
            'files' => array()
        );

        $currentPath = $request->currentPath;

        if ($request->hasfile('files')) {

            $path = '';
            if ($request->path != 'undefined') {
                $path = $request->path;
            }

            $formData = [];
            $formData['multipart'][0]['name'] = 'path';
            $formData['multipart'][0]['contents'] = $path;
            $formData['multipart'][1]['name'] = 'currentPath';
            $formData['multipart'][1]['contents'] = $currentPath;
            $index = 1;
            foreach ($request->file('files') as $key => $value) {
                $name = $path . $value->getClientOriginalName();
                $result->files[$index] = $name;

                $index++;
                $formData['multipart'][$index]['name'] = 'files[]';
                $formData['multipart'][$index]['filename']  = $value->getClientOriginalName();
				$formData['multipart'][$index]['Mime-Type'] = $value->getmimeType();
				$formData['multipart'][$index]['contents']  = fopen($value->getPathname(), 'r' );
            }
            
            $client = new \GuzzleHttp\Client(['headers' => $this->setHeader()]);
            $response = $client->request('POST', env('IONBOARD_API_URL').'/file/'.$uuid.'/fm/upload', $formData);
            $response = $response->getBody()->getContents();
        }

        return $response;
    }

    public function fileManagerDownload($uuid, Request $request)
    {
        $path = '';
        if ($request->filepath != null) {
            $path = $request->filepath;
        }
        $filePath = 'download/' . $uuid . '/' . $path;

        $client = new \GuzzleHttp\Client(['headers' => $this->setHeader()]);
        $response = $client->request('GET', env('IONBOARD_API_URL').'/file/'.$uuid.'/fm/file/download?filepath='.$request->filepath);

        return response()->download(storage_path('app/' . $filePath));
    }

    public function fileManagerMassDownload($uuid, Request $request)
    {
        $project = $this->projectModel->where('uuid', $uuid)->first();
        if ($project == null) return;

        $currentPath = '';
        if ($request->currentpath != null) {
            $currentPath = $request->currentpath;
        }

        $isRoot = false;
        if ($currentPath == '' || $currentPath == '/' || $currentPath == '//') {
            $isRoot = true;
        }

        $reqFiles = json_decode($request->input('files'));

        $zipName = $project->name;
        if (sizeof($reqFiles) == 1) {
            $zipName = basename($reqFiles[0]);
            $zipName = str_replace('/', '_', $zipName);
        }

        $zipfile = storage_path('app/download/download/' . $zipName . '.zip');

        $client = new \GuzzleHttp\Client(['headers' => $this->setHeader()]);
        $response = $client->request('GET', env('IONBOARD_API_URL').'/file/'.$uuid.'/fm/files/download?currentpath='.$request->currentpath.'&files='.$request->input('files'));

        return response()->download($zipfile);
    }

    public function fileManagerRename($uuid, Request $request)
    {
        $client = new \GuzzleHttp\Client(['headers' => $this->setHeader()]);
        $response = $client->request('POST', env('IONBOARD_API_URL').'/file/'.$uuid.'/fm/file/rename', ['form_params' => $request->all()]);
        $response = $response->getBody()->getContents();
        $response = json_decode($response, true);
        
        return response()->json($response, 200);
    }

    private function isValidFileName($fileName)
    {
        $onlyName = pathinfo($fileName, PATHINFO_FILENAME);;

        if (preg_match('/^[\w\-. ]+$/m', $onlyName)) {
            return true;
        }

        return false;
    }

    public function fileManagerDelete($uuid, Request $request)
    {
        $client = new \GuzzleHttp\Client(['headers' => $this->setHeader()]);
        $response = $client->request('POST', env('IONBOARD_API_URL').'/file/'.$uuid.'/fm/file/delete', ['form_params' => $request->all()]);
        $response = $response->getBody()->getContents();
        $response = json_decode($response, true);
        
        return response()->json($response, 200);
    }

    public function fileManagerMassDelete($uuid, Request $request)
    {
        $client = new \GuzzleHttp\Client(['headers' => $this->setHeader()]);
        $response = $client->request('POST', env('IONBOARD_API_URL').'/file/'.$uuid.'/fm/files/delete', ['form_params' => $request->all()]);
        $response = $response->getBody()->getContents();
        $response = json_decode($response, true);
        
        return response()->json($response, 200);
    }

    public function fileManagerFolderDelete($uuid, Request $request)
    {
        $client = new \GuzzleHttp\Client(['headers' => $this->setHeader()]);
        $response = $client->request('POST', env('IONBOARD_API_URL').'/file/'.$uuid.'/fm/folder/delete', ['form_params' => $request->all()]);
        $response = $response->getBody()->getContents();
        $response = json_decode($response, true);
        
        return response()->json($response, 200);
    }

    public function delete($uuid)
    {
        DB::beginTransaction();

        try {
            $project = $this->projectModel->where('uuid', $uuid)->first();
            $project->delete();

            $project->userProject()->delete();
            $project->companyProject()->delete();

            DB::commit();

            return redirect()->route('project.index')->with([
                'code' => 200,
                'message' => 'Project has been deleted.'
            ]);
        } catch (Exception $e) {
            DB::rollback();

            return redirect()->route('project.index')->withErrors($e)->withInput()->with([
                'code' => 400,
                'message' => 'Failed to delete project.'
            ]);
        }
    }

    private function setHeader()
    {
        $user = Auth::user();
        if (!$user) {
            return;
        }

        return [
            'Authorization' => 'Bearer '.$user->api_token
        ];
    }
}
