<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Session;

/**
 * Restrict a route to specific staff roles.
 *
 * Usage: ->middleware('role:admin') or ->middleware('role:manager,admin')
 */
class RequireRole
{
    public function handle($request, Closure $next, string ...$roles)
    {
        if (in_array(Session::get('staff.role'), $roles, true)) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to perform this action.',
            ], 403);
        }

        return redirect()->route('dashboard')
            ->with('error', 'You do not have permission to access that page.');
    }
}
