<?php

namespace App\Http\Middleware;

use App\Models\Staff;
use Closure;
use Illuminate\Support\Facades\Session;

class AuthStaff
{
    public function handle($request, Closure $next)
    {
        // Check if staff session exists
        if (!Session::has('staff.id')) {
            return $this->unauthenticated($request);
        }

        // Re-check the account on every request so that deactivated staff are
        // logged out and role changes take effect without a re-login.
        $staff = Staff::query()
            ->select(['id', 'name', 'role', 'is_active'])
            ->find(Session::get('staff.id'));

        if (!$staff || !$staff->is_active) {
            Session::flush();
            return $this->unauthenticated($request, 'Your session has ended. Please log in again.');
        }

        if (Session::get('staff.role') !== $staff->role || Session::get('staff.name') !== $staff->name) {
            Session::put('staff', [
                'id'   => $staff->id,
                'name' => $staff->name,
                'role' => $staff->role,
            ]);
        }

        return $next($request);
    }

    private function unauthenticated($request, ?string $message = null)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $message ?? 'Authentication required. Please login again.',
            ], 401);
        }

        return redirect()->route('login')->with('error', $message);
    }
}
