<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Staff;

class StaffController extends Controller
{
    /**
     * Display a paginated list of staff with stats.
     */
    public function index(Request $request)
    {
        $query = Staff::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(fn($q) => 
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('role', 'LIKE', "%{$search}%")
            );
        }

        $staffs = $query->orderBy('created_at', 'desc')->paginate(15);

        $stats = Staff::selectRaw('COUNT(*) as total,
                                  SUM(is_active) as active,
                                  SUM(!is_active) as inactive')
                     ->first();

        return view('users-admin', [
            'users' => $staffs,
            'totalUsers' => $stats->total,
            'activeUsers' => $stats->active,
            'inactiveUsers' => $stats->inactive,
        ]);
    }

    /**
     * Store a new staff.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:15|regex:/^[0-9]{10,15}$/',
            'username' => 'required|string|max:50|unique:staffs',
            'password' => 'required|string|min:6',
            'role' => 'required|string|in:admin,manager,cashier',
            'is_active' => 'required|boolean',
        ]);

        Staff::create($request->only('name', 'phone', 'username', 'password', 'role', 'is_active'));

        return redirect()->back()->with('success', 'Staff added successfully!');
    }

    /**
     * Update an existing staff.
     */
    public function update(Request $request, Staff $staff)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:15|regex:/^[0-9]{10,15}$/',
            'username' => 'required|string|max:50|unique:staffs,username,' . $staff->id,
            'role' => 'required|string|in:admin,manager,cashier',
            'is_active' => 'required|boolean',
            'password' => 'nullable|string|min:6',
        ]);

        $data = $request->only('name', 'phone', 'username', 'role', 'is_active');

        if ($request->filled('password')) {
            $data['password'] = $request->password; // ensure model mutator hashes it
        }

        $staff->update($data);

        return redirect()->back()->with('success', 'Staff updated successfully!');
    }
}
