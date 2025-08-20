<?php

namespace App\Http\Controllers;

use App\Company;
use App\Project;
use Illuminate\Http\Request;

use App\Http\Requests\StoreCompanyRequest;

use DataTables;
use Auth;
use DB;

class CompanyController extends Controller
{
    private $companyModel;
    private $projectModel;

    public function __construct(Company $company, Project $project)
    {
        $this->companyModel = $company;
        $this->projectModel = $project;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if(Auth::user()->role == \App\User::ROLE_ADMIN){
            return view('company.index');
        }else{
            return redirect()->route('home');
        }
    }

    /**
     * Show list data by datatable request
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Datatable
     */
    public function listData(Request $request)
    {
        DB::statement(DB::raw('set @rownum='.$request->start));
        $model = $this->companyModel->select([
            DB::raw('@rownum  := @rownum  + 1 AS rownum'),
            'uuid', 'name',
            'created_at', 'updated_at',
        ])
        ;

        $datatables = DataTables::of($model);

        $datatables->editColumn('action', function ($model) {
            $html = '';
            $html .= '<a href="'.route('company.view', ['company' => $model->uuid]).'" class="btn btn-primary"><i class="fa fa-info"></i> View</a>';
            $html .= '&nbsp;<a href="'.route('company.edit', ['company' => $model->uuid]).'" class="btn btn-success"><i class="fa fa-pencil"></i> Edit</a>';
            $html .= '&nbsp;<a href="javascript:;" class="btn btn-danger btn-delete" companyid="'.$model->uuid.'" name="'.$model->name.'"><i class="fa fa-trash"></i> Delete</a>';

            return $html;
        });

        return $datatables->make(true);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $projects = $this->projectModel->orderBy('name', 'asc')->get();

        return view('company.form', ['projects' => $projects]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     * 
     * TODO: Delete previous user selected project
     */
    public function store(StoreCompanyRequest $request)
    {
        DB::beginTransaction();
        try {
            $company = $this->companyModel->firstOrNew(['uuid' => $request->uuid]);

            $company->uuid = $request->uuid != 'add' ? $request->uuid : \Uuid::generate();
            $company->name = $request->name;
            if ($request->logo) {
                if (!$path = $request->logo->store('images')) {
                    $message = 'Failed to ';
                    $message .= $request->uuid ? 'update' : 'create';
                    $message .= ' logo for company ['.$company->name.'].';

                    return redirect()->back()->withInput()->with([
                        'code' => 400,
                        'message' => $message,
                    ]);
                }
                $company->logo = $path;
            }
            $company->save();

            DB::commit();
            $message = 'Company ['.$company->name.'] has been successfullly ';
            $message .= $request->uuid ? 'updated' : 'created';
            $message .= '.';

            return redirect(route('company.index'))->with([
                'code' => 200,
                'message' => $message,
            ]);
        } catch (Exception $e) {
            DB::rollback();
            $message = 'Failed to ';
            $message .= $request->uuid ? 'update' : 'create';
            $message .= ' company ['.$company->name.'].';
            
            return redirect()->back()->withErrors($e)->withInput()->with([
                'code' => 400,
                'message' => $message,
            ]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($uuid)
    {
        $company = $this->companyModel->where('uuid', $uuid)->first();
        $projects = $this->projectModel->orderBy('name', 'asc')->get();

        return view('company.form', ['company' => $company, 'projects' => $projects]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function delete($uuid)
    {
        DB::beginTransaction();
        
        try {
            $company = $this->companyModel->where('uuid', $uuid)->first();
            if ($company->project()->count() > 0) {
                return redirect()->route('company.index')->withInput()->with([
                    'code' => 400,
                    'message' => 'There are still ['.$company->project()->count().'] projects aligned to ['.$company->name.'] company.'
                ]);    
            }
            if ($company->user()->count() > 0) {
                return redirect()->route('company.index')->withInput()->with([
                    'code' => 400,
                    'message' => 'There are still ['.$company->user()->count().'] users aligned to ['.$company->name.'] company.'
                ]);    
            }
            $company->delete();

            DB::commit();

            return redirect()->route('company.index')->with([
                'code' => 200,
                'message' => 'Company has been deleted.'
            ]);
        } catch (Exception $e) {
            DB::rollback();

            return redirect()->route('company.index')->withErrors($e)->withInput()->with([
                'code' => 400,
                'message' => 'Failed to delete company.'
            ]);
        }
    }

    /**
     * Show the detail for view the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show($uuid)
    {
        $company = $this->companyModel->where('uuid', $uuid)->first();

        return view('company.view', ['company' => $company]);
    }

    public function listProjectCompany($companyId, Request $request)
    {
        $company = $this->companyModel->where('uuid', $companyId)->first();
        if (!$company) {
            $datatables = DataTables::collection([]);

            return $datatables->make(true);
        }

        DB::statement(DB::raw('set @rownum='.$request->start));
        $model = $this->companyModel->where('uuid', $companyId)->first()->project()->select([
            DB::raw('@rownum  := @rownum  + 1 AS rownum'),
            'code', 'projects.name', 'description', 'objective', 'start_date', 'finish_date', 'timeline', 'respondent',
            'coverage', 'methodology', 'timeline',
            'projects.created_at', 'projects.updated_at',
        ])
        ;

        $datatables = DataTables::of($model);

        return $datatables->make(true);
    }

    public function listCompanyUser($companyId, Request $request)
    {
        $company = $this->companyModel->where('uuid', $companyId)->first();
        if (!$company) {
            $datatables = DataTables::collection([]);

            return $datatables->make(true);
        }
        
        DB::statement(DB::raw('set @rownum='.$request->start));
        $model = $this->companyModel->where('uuid', $companyId)->first()->user()->select([
            DB::raw('@rownum  := @rownum  + 1 AS rownum'),
            'name', 'email',
            'created_at', 'updated_at',
        ])
        ;

        $datatables = DataTables::of($model);

        return $datatables->make(true);        
    }

    public function getCompanyProject($companyId)
    {
        $company = $this->companyModel->where('uuid', $companyId)->first();
        $companyProject = [];
        if ($company->project) {
            foreach ($company->project as $key => $value) {
                $companyProject[$key]['id'] = $value->uuid;
                $companyProject[$key]['text'] = $value->name;
            }
        }

        $data = [
            'results' => $companyProject,
        ];
        
        return response()->json($data);
    }
}
