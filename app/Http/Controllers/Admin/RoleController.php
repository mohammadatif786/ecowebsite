<?php

namespace App\Http\Controllers\Admin;

use Inertia\Inertia;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $allRoles = Role::query()
            ->where('name', '!=', 'admin')
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->orderBy('created_at', 'DESC')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('admin/roles/Index', [
            'allroles' => $allRoles,
            'filters' => $request->only('search'),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $allPermission = [];
        $permissions = Permission::get();
        foreach ($permissions as $permission) {
            $allPermission[$permission->name] = $permission->name;
        }

        return Inertia::render('admin/roles/Create', [
            'allPermission' => $allPermission,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $newPermissions = $request->input('permission');
        $allPermissions = [];
        foreach ($newPermissions as $key =>  $permission) {
            $allPermissions[] = $key;
        }

        $role = Role::create(['name' => $request->input('name')]);
        $role->syncPermissions($allPermissions);
        return redirect()->route('admin.roles.index')->with('message', 'Role created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $allPermission = [];
        $Permissioncheck = [];
        $roledetail = Role::whereId($id)->select()->get();
        $role = Role::findById($id);
        $permissions = $role->permissions()->get();
        $permissions1 = Permission::get();
        foreach ($permissions1 as $permission) {
            $allPermission[$permission->name] = $permission->name;
        }
        foreach ($permissions as $permission) {
            $Permissioncheck[$permission->name] = $permission->name;
        }
        return Inertia::render('admin/roles/Edit', [
            'role' => $role,
            'allPermission' => $allPermission,
            'Permissioncheck' => $Permissioncheck,
            'roledetail' => $roledetail,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Role $role)
    {
        if ($request->name) {
            $role->update(['name' => $request->name]);
        }

        $input = $request->permission;
        $role->syncPermissions($input);

        return redirect()->route('admin.roles.index')->with('message', 'Role updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role)
    {
        if ($role->name == 'admin') {
            return redirect()->route('admin.roles.index')->with('message', 'You cannot delete the admin role');
        }
        $role->delete();
        return redirect()->route('admin.roles.index')->with('message', 'Role deleted successfully');
    }
}
