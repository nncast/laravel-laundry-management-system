<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class StaffController extends Controller
{
    /**
     * Display a paginated list of staff with stats.
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));

        $staffs = Staff::query()
            ->when($search !== '', fn ($query) => $query->where(fn ($q) =>
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('username', 'LIKE', "%{$search}%")
                  ->orWhere('role', 'LIKE', "%{$search}%")
            ))
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        // CASE works on both MySQL and SQLite (SUM(!col) does not)
        $stats = Staff::selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active')
            ->first();

        return view('users-admin', [
            'users' => $staffs,
            'totalUsers' => (int) $stats->total,
            'activeUsers' => (int) $stats->active,
            'inactiveUsers' => (int) $stats->total - (int) $stats->active,
        ]);
    }

    /**
     * Store a new staff.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => ['nullable', 'regex:/^[0-9]{10,15}$/'],
            'username' => 'required|string|max:50|unique:staffs,username',
            'password' => 'required|string|min:6',
            'role' => 'required|string|in:admin,manager,cashier',
            'is_active' => 'required|boolean',
        ], [
            'phone.regex' => 'Phone number must be 10 to 15 digits.',
        ]);

        Staff::create($validated);

        return redirect()->back()->with('success', 'Staff added successfully!');
    }

    /**
     * Update an existing staff.
     */
    public function update(Request $request, Staff $staff)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => ['nullable', 'regex:/^[0-9]{10,15}$/'],
            'username' => 'required|string|max:50|unique:staffs,username,' . $staff->id,
            'role' => 'required|string|in:admin,manager,cashier',
            'is_active' => 'required|boolean',
            'password' => 'nullable|string|min:6',
        ], [
            'phone.regex' => 'Phone number must be 10 to 15 digits.',
        ]);

        $isSelf = $staff->id === (int) Session::get('staff.id');
        $losesAdmin = $staff->role === 'admin'
            && ($validated['role'] !== 'admin' || !$request->boolean('is_active'));

        if ($isSelf && $losesAdmin) {
            return back()->with('error', 'You cannot deactivate your own account or remove your own admin role.');
        }

        if ($losesAdmin && $this->activeAdminCount($staff->id) === 0) {
            return back()->with('error', 'At least one active admin account is required.');
        }

        $data = collect($validated)->except('password')->all();
        if ($request->filled('password')) {
            $data['password'] = $validated['password']; // hashed by the model mutator
        }

        $staff->update($data);

        return redirect()->back()->with('success', 'Staff updated successfully!');
    }

    /**
     * Delete a staff account (only if it has no orders; otherwise deactivate it).
     */
    public function destroy(Staff $staff)
    {
        if ($staff->id === (int) Session::get('staff.id')) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        // orders.staff_id cascades on delete – never lose order history
        if (Order::where('staff_id', $staff->id)->exists()) {
            return back()->with('error', 'This staff member has orders on record. Deactivate the account instead of deleting it.');
        }

        if ($staff->role === 'admin' && $staff->is_active && $this->activeAdminCount($staff->id) === 0) {
            return back()->with('error', 'At least one active admin account is required.');
        }

        $staff->delete();

        return back()->with('success', 'Staff deleted successfully!');
    }

    private function activeAdminCount(int $exceptId): int
    {
        return Staff::where('role', 'admin')
            ->where('is_active', true)
            ->where('id', '!=', $exceptId)
            ->count();
    }
}
