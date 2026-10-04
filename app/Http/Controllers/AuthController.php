<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Staff;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    public function showLogin()
    {
        // If already logged in, skip login page
        if (Session::has('staff.id')) {
            return redirect()->route('dashboard');
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
            return back()->withInput($request->only('username'))
                ->with('error', 'Invalid username or password.');
        }

        if (!$staff->is_active) {
            return back()->withInput($request->only('username'))
                ->with('error', 'Your account is deactivated.');
        }

        // Prevent session fixation, then store the staff in the new session
        Session::regenerate();
        Session::put('staff', [
            'id'   => $staff->id,
            'name' => $staff->name,
            'role' => $staff->role,
        ]);

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request)
    {
        Session::flush();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
