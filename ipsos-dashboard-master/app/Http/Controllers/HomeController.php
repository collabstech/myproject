<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Project;
use App\User;
use Auth;
use DataTables;

class HomeController extends Controller
{
    private $projectModel;
    
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(Project $project)
    {
        $this->projectModel = $project;
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('home');
    }

    public function listProject(Request $request)
    {
        $user = Auth::user();
        $userProject = [];
        if (isset($user->userProject)) {
            foreach ($user->userProject as $key => $value) {
                $userProject[$value->project_id] = $value->project_id;
            }
        }

        \DB::statement(\DB::raw('set @rownum='.(int) $request->start));
        $model = $this->projectModel->select([
            \DB::raw('@rownum  := @rownum  + 1 AS rownum'),
            'projects.id', 'projects.uuid', 'projects.name', 'objective', 'start_date', 'finish_date',
            'company_id',
            'projects.created_at', 'projects.updated_at',
        ])
        ->with('company')
        ;

        if ($user->role != User::ROLE_ADMIN) {
            $model = $model->whereIn('id', $userProject);
        } else {
            $model = $model->limit(10);
        }

        $datatables = DataTables::of($model);

        $datatables->editColumn('start_date', function ($model) {
            $date = new \DateTime($model->start_date);
            return $date->format('Y-m-d');
        });

        $datatables->editColumn('finish_date', function ($model) {
            $date = new \DateTime($model->finish_date);
            return $date->format('Y-m-d');
        });
        
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
}
