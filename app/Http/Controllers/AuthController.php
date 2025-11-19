<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Staff; // <-- changed from User
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    public function showLogin()
    {
        // If already logged in, skip login page
        if (Session::has('staff.id')) {
            return redirect('/home');
        }

        return view('login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $staff = Staff::where('username', $request->username)->first();

        if (!$staff || !Hash::check($request->password, $staff->password)) {
            return back()->with('error', 'Invalid username or password.');
        }

        if (!$staff->is_active) {
            return back()->with('error', 'Your account is deactivated.');
        }

        // Store in session
        Session::put('staff', [
            'id'   => $staff->id,
            'name' => $staff->name,
            'role' => $staff->role,
        ]);

        Session::regenerate();

        return redirect('/home');
    }

    public function logout()
    {
        Session::flush();
        return redirect()->route('login');
    }
}
