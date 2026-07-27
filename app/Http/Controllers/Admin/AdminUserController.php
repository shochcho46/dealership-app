<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class AdminUserController extends Controller
{
    /**
     * Display a listing of admin users with search filtering
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $admins = Admin::with('roles')
            ->whereDoesntHave('roles', function($query) {
                $query->where('name', 'SuperAdmin');
            })->orderBy('id', 'desc');

        // Apply search filter if search term exists
        if ($search) {
            $admins->where(function($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                      ->orWhere('email', 'like', '%' . $search . '%')
                      ->orWhere('phone', 'like', '%' . $search . '%');
            });
        }

        $admins = $admins->paginate(20);
        return view('admin.admin-user.index', compact('admins', 'search'));
    }

    /**
     * Show the form for creating a new admin user
     */
    public function create()
    {
        $roles = Role::where('guard_name', 'admin')->where('name', '!=', 'SuperAdmin')->get();
        return view('admin.admin-user.create', compact('roles'));
    }

    /**
     * Store a newly created admin user in database
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:admins,email',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'role' => 'required|exists:roles,id',
            'status' => 'nullable|boolean',
            'sales_target' => 'nullable|numeric|min:0'
        ]);

        try {
            $admin = Admin::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => bcrypt($validated['password']),
                'phone' => $validated['phone'] ?? null,
                'status' => $request->has('status') ? 1 : 0,
                'sales_target' => $validated['sales_target'] ?? null
            ]);

            // Assign role to admin
            $role = Role::find($validated['role']);
            $admin->assignRole($role);

            return redirect()->route('admin.adminUserIndex')
                ->with('success', 'Admin user created successfully with role: ' . $role->name);
        } catch (\Exception $e) {
            return back()->with('error', 'Error creating admin user: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Show the form for editing the specified admin user
     */
    public function edit(Admin $admin)
    {
        $roles = Role::where('guard_name', 'admin')->get();
        $selectedRoles = $admin->roles->pluck('id')->toArray();
        return view('admin.admin-user.edit', compact('admin', 'roles', 'selectedRoles'));
    }

    /**
     * Update the specified admin user in database (with optional password update)
     */
    public function update(Request $request, Admin $admin)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:admins,email,' . $admin->id,
            'password' => 'nullable|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'role' => 'required|exists:roles,id',
            'status' => 'nullable|boolean',
            'sales_target' => 'nullable|numeric|min:0'
        ]);

        try {
            $updateData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'status' => $request->has('status') ? 1 : 0,
                'sales_target' => $validated['sales_target'] ?? null
            ];

            // Only update password if provided
            if (!empty($validated['password'])) {
                $updateData['password'] = bcrypt($validated['password']);
            }

            $admin->update($updateData);

            // Sync roles
            $role = Role::find($validated['role']);
            $admin->syncRoles($role);

            return redirect()->route('admin.adminUserIndex')
                ->with('success', 'Admin user updated successfully with role: ' . $role->name);
        } catch (\Exception $e) {
            return back()->with('error', 'Error updating admin user: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Delete the specified admin user
     */
    public function destroy(Admin $admin)
    {
        try {
            // Prevent deleting the only admin user
            if (Admin::count() === 1) {
                return back()->with('error', 'Cannot delete the last admin user.');
            }

            $adminName = $admin->name;
            $admin->delete();

            return redirect()->route('admin.adminUserIndex')
                ->with('success', "Admin user '$adminName' deleted successfully.");
        } catch (\Exception $e) {
            return back()->with('error', 'Error deleting admin user: ' . $e->getMessage());
        }
    }

    /**
     * Show the specified admin user details
     */
    public function show(Admin $admin)
    {
        $admin->load('roles');
        return view('admin.admin-user.show', compact('admin'));
    }

    /**
     * Toggle admin user status (Active/Inactive)
     */
    public function toggleStatus(Admin $admin)
    {
        try {
            $admin->update(['status' => !$admin->status]);
            $status = $admin->status ? 'activated' : 'deactivated';
            return redirect()->route('admin.adminUserIndex')
                ->with('success', "Admin user has been $status successfully.");
        } catch (\Exception $e) {
            return back()->with('error', 'Error updating status: ' . $e->getMessage());
        }
    }
}
