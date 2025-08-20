<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class LogoutController extends Controller
{
    public function postLogout()
    {
        try {
            Auth::logout();

            return redirect('/login');
        } catch (Exception $e) {
            return redirect('/')->withErrors()->withInput();            
        }
    }
}
