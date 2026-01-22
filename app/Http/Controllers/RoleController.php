<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\HcisEmployee;
use App\Models\User;

class RoleController extends Controller
{
    private function getPermissionsList() {
        return collect([
            (object)['id' => 'view_admin_setting', 'label' => 'Access Role Setting', 'group' => 'Admin', 'section' => 'Settings'],
            (object)['id' => 'view_import_center', 'label' => 'Access Import Center', 'group' => 'Menu', 'section' => 'Tools'],
        ]);
    }

    public function index()
    {
        $filterData = [
            'businessUnits' => HcisEmployee::select('group_company')->whereNotNull('group_company')->distinct()->orderBy('group_company')->get(),
            'companies' => HcisEmployee::select('company_name')->whereNotNull('company_name')->distinct()->orderBy('company_name')->get(),
            'locations' => HcisEmployee::select('office_area')->whereNotNull('office_area')->distinct()->orderBy('office_area')->get(),
        ];
        
        $employees = HcisEmployee::orderBy('fullname')->get();
        $roles = Role::with('users.employee')->get();
        $permissions = $this->getPermissionsList()->groupBy(['group', 'section']);
        
        $lockedEmployees = [];
        foreach ($employees as $employee) {
            $user = User::where('employee_id', $employee->employee_id)->first();
            if ($user && $user->roles->count() > 0) {
                $lockedEmployees[$employee->employee_id] = $user->roles->pluck('name')->join(', ');
            }
        }

        return view('admin.roles.index', compact('filterData', 'employees', 'roles', 'permissions', 'lockedEmployees'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'role_name' => 'required|string|unique:roles,name',
            'permissions' => 'nullable|array',
            'employee_ids' => 'nullable|array'
        ]);

        $role = Role::create([
            'name' => $validated['role_name'],
            'business_unit' => $request->business_unit ?? [],
            'company' => $request->company ?? [],
            'location' => $request->location ?? [],
            'permissions' => $validated['permissions'] ?? [] // Simpan JSON
        ]);

        if (!empty($request->employee_ids)) {
            $userIds = User::whereIn('employee_id', $request->employee_ids)->pluck('id');
            $role->users()->sync($userIds);
        }

        return redirect()->route('roles.index')->with('success', 'Role Created!');
    }

    public function update(Request $request, Role $role)
    {
        if (in_array($role->name, ['Manager', 'Superior'])) {
            $role->update(['permissions' => $request->permissions ?? []]);
            return back()->with('success', 'Permissions updated!');
        }

        $role->update([
            'name' => $request->role_name,
            'business_unit' => $request->business_unit ?? [],
            'company' => $request->company ?? [],
            'location' => $request->location ?? [],
            'permissions' => $request->permissions ?? []
        ]);

        $userIds = User::whereIn('employee_id', $request->employee_ids ?? [])->pluck('id');
        $role->users()->sync($userIds);

        return back()->with('success', 'Role Updated!');
    }

    public function destroy(Role $role)
    {
        if (in_array($role->name, ['Manager', 'Superior'])) return back()->with('error', 'Cannot delete system role.');
        $role->users()->detach();
        $role->delete();
        return back()->with('success', 'Role Deleted.');
    }
    
    // API Filter User
    public function filterEmployees(Request $request) {
        $q = HcisEmployee::query();
        if ($request->business_unit) $q->whereIn('group_company', $request->business_unit);
        if ($request->location) $q->whereIn('office_area', $request->location);
        return response()->json($q->orderBy('fullname')->get(['employee_id', 'fullname']));
    }
}