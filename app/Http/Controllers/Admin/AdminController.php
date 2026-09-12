<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminUserRequest;
use App\Models\CaribbeanIsland;
use App\Models\CountryPhoneCode;
use App\Models\Nationality;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class AdminController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $admins = User::query()->where('type', 'admin')
            ->filter($request->only('search'))
            ->orderBy('created_at', 'DESC')
            ->paginate(10);

        return Inertia::render('admin/adminlist/Index', [
            'admins' => $admins,
            'filters' => $request->only('search'),
            'message' => session('message'),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $phoneCodes = CountryPhoneCode::select([
            'code as value',
            DB::raw("CONCAT(code, ', ', name) as label")
        ])->get();
        $nationalityList = Nationality::select(['name as value', 'name as label'])->get();
        $caribbeanIslandList = CaribbeanIsland::select(['name as value', 'name as label'])->get();
        $roles = Role::get();
        return Inertia::render('admin/adminlist/Component/Create', [
            'nationalities' => $nationalityList,
            'caribbean_island' => $caribbeanIslandList,
            'phone_codes' => $phoneCodes,
            'roles' => $roles
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(AdminUserRequest $request)
    {
        $data = $request->all();
        $data['type'] = $data['type'];
        if (isset($data['avatar']) && $request->hasFile('avatar')) {
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        } else {
            $data['avatar'] = null;
        }

        $user = User::create($data);
        $user->syncRoles([$data['type']]);
        $role = Role::where('name', $data['type'])->first();
        if ($role) {
            $user->syncPermissions($role->permissions);
        }
        return redirect()->route('admin.admins.index')->withSuccess('Admin created successfully.');
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
        $user = User::find($id);
        $phoneCodes = CountryPhoneCode::select([
            'code as value',
            DB::raw("CONCAT(code, ', ', name) as label")
        ])->get();
        $nationalityList = Nationality::select(['name as value', 'name as label'])->get();
        $caribbeanIslandList = CaribbeanIsland::select(['name as value', 'name as label'])->get();
        $roles = Role::get();

        return Inertia::render('admin/adminlist/Component/Edit', [
            'user' => $user,
            'nationalities' => $nationalityList,
            'caribbean_island' => $caribbeanIslandList,
            'phone_codes' => $phoneCodes,
            'roles' => $roles
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(AdminUserRequest $request, User $admin)
    {
        $data = $request->all();
        $data['type'] = $data['type'];
        $data['password']= $admin->password;
        if (isset($data['avatar']) && $request->hasFile('avatar')) {
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        } else {
            $data['avatar'] = $admin->avatar;
        }

        $admin->update($data);
        $admin->syncRoles([$data['type']]);
        $role = Role::where('name', $data['type'])->first();
        if ($role) {
            $admin->syncPermissions($role->permissions);
        }
        return redirect()->route('admin.admins.index')->withSuccess('Admin Updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $admin)
    {
        $admin->delete();
        return redirect()->back()->withSuccess('Admin successfully deleted.');
    }
}
