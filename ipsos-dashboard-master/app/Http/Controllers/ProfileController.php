<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Http\Requests\ProfileSaveRequest;

class ProfileController extends Controller
{
    public function updateProfile(ProfileSaveRequest $request)
    {
        $user = Auth::user();

        try {
            $user->email = $request->email;
            $user->name = $request->name;
            if ($request->avatar) {
                $path = $request->avatar->store('images');
                $user->avatar = $path;
            }
            if ($request->password) {
                $user->password = bcrypt($request->password);
            }
            $user->save();

            return redirect('/')->with([
                'code' => 200,
                'message' => 'Your profile was changed successfully.'
            ]);
        } catch (Exception $e) {
            return redirect('/')->withErrors($e)->withInput()->with([
                'code' => 400,
                'message' => 'Your profile failed to change.'
            ]);
        }
    }
}
