<?php

namespace App\Http\Controllers;

use App\User;
use App\Company;
use App\Project;
use Illuminate\Http\Request;

use App\Http\Requests\StoreUserRequest;

use DataTables;
use DB;
use Auth;

class UserController extends Controller
{
    private $userModel;
    private $companyModel;
    private $projectModel;

    public function __construct(User $user, Company $company, Project $project)
    {
        $this->userModel = $user;
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
            return view('user.index');
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
        $model = $this->userModel->select([
            DB::raw('@rownum  := @rownum  + 1 AS rownum'),
            'company_id', 'uuid', 'name', 'email', 'avatar', 'role', 'status',
            'created_by', 
            'created_at', 'updated_at',
        ])
        ->with('company')
        ;

        $datatables = DataTables::of($model);

        $datatables->editColumn('rolename', function ($model) {
            return User::roleLabel()[$model->role];
        });

        $datatables->editColumn('created_by_name', function ($model) {
            return $model->created_by_name;
        });
        
        $datatables->editColumn('action', function ($model) {
            $html = '';
            $html .= '<a href="'.route('user.edit', ['user' => $model->uuid]).'" class="btn btn-primary"><i class="fa fa-pencil"></i> Edit</a>';

            if($model->role != 1 && Auth::user()->id != $model->id){
                if ($model->status == User::STATUS_ACTIVE) {
                    $html .= '&nbsp;<a href="javascript:;" class="btn btn-warning btn-block-user" userid="'.$model->uuid.'" name="'.$model->name.'"><i class="fa fa-ban"></i> Block</a>';
                } else {
                    $html .= '&nbsp;<a href="javascript:;" class="btn btn-success btn-unblock-user" userid="'.$model->uuid.'" name="'.$model->name.'"><i class="fa fa-check"></i> Unblock</a>';
                }
                $html .= '&nbsp;<a href="javascript:;" class="btn btn-danger btn-delete" userid="'.$model->uuid.'" name="'.$model->name.'"><i class="fa fa-trash"></i> Delete</a>';
            }

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
        $companies = $this->companyModel->orderBy('name', 'asc')->get();
        $projects = $this->projectModel->orderBy('name', 'asc')->get();

        return view('user.form', ['companies' => $companies, 'projects' => $projects]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreUserRequest $request)
    {
        $auth = \Auth::user();
        DB::beginTransaction();
        try {
            $user = $this->userModel->firstOrNew(['uuid' => $request->uuid]);
            $company = $this->companyModel->where('uuid', $request->company_id)->first();

            $user->uuid = $user->exists ? $request->uuid : \Uuid::generate();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->company_id = $company->id;
            if ($request->password) {
                $user->password = bcrypt($request->password);
            }
            $user->role = $request->role;
            $user->remember_token = str_random(10);
            if ($request->avatar) {
                if (!$path = $request->avatar->store('images')) {
                    $message = 'Failed to ';
                    $message .= $request->uuid ? 'update' : 'create';
                    $message .= ' profile picture user ['.$user->name.'].';

                    return redirect()->back()->withInput()->with([
                        'code' => 400,
                        'message' => $message,
                    ]);
                }
                $user->avatar = $path;
            }
            if (!$user->exists) {
                $user->created_by = $auth->id;
            }
            $user->updated_by = $auth->id;
            $user->save();

            $user->userProject()->delete();
            if ($request->project) {
                // Delete previous selected project
                foreach ($request->project as $key => $value) {
                    $project = $this->projectModel->where('uuid', $value)->first();

                    if ($project) {
                        $userProject = $user->userProject()->create([         
                            'user_id' => $user->id,
                            'project_id' => $project->id,
                        ]);
                    }
                }
            }

            DB::commit();
            $message = 'User ['.$user->name.'] has been successfullly ';
            $message .= $request->uuid ? 'updated' : 'created';
            $message .= '.';

            return redirect(route('user.index'))->with([
                'code' => 200,
                'message' => $message,
            ]);
        } catch (Exception $e) {
            DB::rollback();
            $message = 'Failed to ';
            $message .= $request->uuid ? 'update' : 'create';
            $message .= ' user ['.$user->name.'].';
            
            return redirect()->back()->withErrors($e)->withInput()->with([
                'code' => 400,
                'message' => $message,
            ]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function edit($uuid)
    {
        $user = $this->userModel->where('uuid', $uuid)->first();
        $companies = $this->companyModel->orderBy('name', 'asc')->get();

        $company = $user->company;
        $companyProject = [];
        if (isset($company->project)) {
            foreach ($company->project as $key => $value) {
                $companyProject[$value->id] = $value->id;
            }
        }

        $projects = $this->projectModel->whereIn('id', $companyProject)->orderBy('name', 'asc')->get();

        $userProject = [];
        if (isset($user->userProject)) {
            foreach ($user->userProject as $key => $value) {
                $userProject[$value->project_id] = $value;
            }
        }

        return view('user.form', ['user' => $user, 'companies' => $companies, 'projects' => $projects, 'userProject' => $userProject]);
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
            $user = $this->userModel->where('uuid', $uuid)->first();
            if ($user->userProject()->count() > 0) {
                return redirect()->route('user.index')->withInput()->with([
                    'code' => 400,
                    'message' => 'There are still ['.$user->userProject()->count().'] projects aligned to ['.$user->name.'] user.'
                ]);    
            }
            $user->delete();

            DB::commit();

            return redirect()->route('user.index')->with([
                'code' => 200,
                'message' => 'User has been deleted.'
            ]);
        } catch (Exception $e) {
            DB::rollback();

            return redirect()->route('user.index')->withErrors($e)->withInput()->with([
                'code' => 400,
                'message' => 'Failed to delete user.'
            ]);
        }
    }

    /**
     * Block the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function block($uuid)
    {
        DB::beginTransaction();
        
        try {
            $user = $this->userModel->where('uuid', $uuid)->first();
            $user->status = User::STATUS_BLOCK;
            $user->save();

            DB::commit();

            return redirect()->route('user.index')->with([
                'code' => 200,
                'message' => 'User has been blocked.'
            ]);
        } catch (Exception $e) {
            DB::rollback();

            return redirect()->route('user.index')->withErrors($e)->withInput()->with([
                'code' => 400,
                'message' => 'Failed to block user.'
            ]);
        }
    }

    /**
     * Unblock the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function unblock($uuid)
    {
        DB::beginTransaction();
        
        try {
            $user = $this->userModel->where('uuid', $uuid)->first();
            $user->status = User::STATUS_ACTIVE;
            $user->save();

            DB::commit();

            return redirect()->route('user.index')->with([
                'code' => 200,
                'message' => 'User has been unblocked.'
            ]);
        } catch (Exception $e) {
            DB::rollback();

            return redirect()->route('user.index')->withErrors($e)->withInput()->with([
                'code' => 400,
                'message' => 'Failed to unblock user.'
            ]);
        }
    }
}
