<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;

use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function index()
    {
        return view('auth.login');
    }

    public function postLogin(LoginRequest $request)
    {
        try {
            if (!Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
                return redirect()->back()->withErrors([
                    'user' => 'Invalid email or password.',
                ])->withInput();
            }

            $user = Auth::user();
            if ($user->status == \App\User::STATUS_BLOCK) {
                return redirect()->back()->withErrors([
                    'user' => 'User has been blocked.',
                ])->withInput();
            }

            $user->api_token = bcrypt(str_random(32));
            $user->save();
            
            return redirect('/');
        } catch (Exception $e) {
            return redirect()->back()->withErrors($e->getMessage())->withInput();
        }
    }
}
