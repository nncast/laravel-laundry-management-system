<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Session;

class AuthStaff
{
    public function handle($request, Closure $next)
    {
        // Check if staff session exists
        if (!Session::has('staff.id')) {
            return redirect()->route('login');
        }

        return $next($request);
    }
}
