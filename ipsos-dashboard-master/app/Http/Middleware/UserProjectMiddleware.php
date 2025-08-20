<?php

namespace App\Http\Middleware;

use Closure;
use Auth;
use App\User;
use App\Project;

class UserProjectMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $project = Project::where('uuid', $request->uuid)->first();
        $user = Auth::user();
        $userProject = [];
        if (isset($user->userProject)) {
            foreach ($user->userProject as $key => $value) {
                $userProject[$value->project_id] = $value->project_id;
            }
        }
        
        
        if ($user->role != User::ROLE_ADMIN 
            && 
            (
                isset($user->userProject) && $user->userProject()->where('project_id', $project->id)->count() < 1)
            ) {
            return redirect('/')->withInput()->with([
                'code' => 400,
                'message' => 'You are not permitted to view that project.'
            ]);
        }
        
        return $next($request);
    }
}
