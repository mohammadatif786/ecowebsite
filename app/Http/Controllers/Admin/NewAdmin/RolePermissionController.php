<?php

namespace App\Http\Controllers\Admin\NewAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\NewAdmin\RolePermissionRequest;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionController extends Controller
{
    public const MODULES = [
        'Dashboard',
        'Users',
        'Event Management',
        'Event Organizer',
        'LinkUp Eats',
        'E-Wallet',
        'ASUE Drawer Data',
        'Bill Gateway',
        'Marketplace',
        'Swipes',
        'Caribbean 360 News',
        'Emails',
        'Push Notifications',
        'Taxes',
        'Reports',
        'Admins',
        'Settings',
    ];

    public const ACTIONS = ['view', 'create', 'edit', 'approve', 'export', 'delete'];

    public function store(RolePermissionRequest $request)
    {
        $data = $request->validated();

        $role = Role::firstOrNew(['name' => $data['name'], 'guard_name' => 'web']);
        $role->description = $data['description'] ?? null;
        $role->risk_level = $data['risk'];
        $role->save();

        $this->syncModulePermissions($role, $data['permissions']);

        return redirect()->back()->with('message', 'Role saved successfully.');
    }

    public function update(RolePermissionRequest $request, Role $role)
    {
        if ($role->name === 'admin') {
            return redirect()->back()->with('error', 'The admin role cannot be modified.');
        }

        $data = $request->validated();

        $role->update([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'risk_level' => $data['risk'],
        ]);

        $this->syncModulePermissions($role, $data['permissions']);

        return redirect()->back()->with('message', 'Role updated successfully.');
    }

    public function destroy(Role $role)
    {
        if ($role->name === 'admin') {
            return redirect()->back()->with('error', 'The admin role cannot be deleted.');
        }

        if ($role->users()->exists()) {
            return redirect()->back()->with('error', 'Reassign this role\'s users before deleting it.');
        }

        $role->delete();

        return redirect()->back()->with('message', 'Role deleted successfully.');
    }

    private function syncModulePermissions(Role $role, array $rows): void
    {
        $permissionNames = [];

        foreach ($rows as $row) {
            $slug = Str::slug($row['module']);
            foreach (self::ACTIONS as $action) {
                if (! empty($row[$action])) {
                    $permissionNames[] = "{$slug}.{$action}";
                }
            }
        }

        foreach ($permissionNames as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        $role->syncPermissions($permissionNames);
    }
}
