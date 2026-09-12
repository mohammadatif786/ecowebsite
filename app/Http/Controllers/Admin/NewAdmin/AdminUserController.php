<?php

namespace App\Http\Controllers\Admin\NewAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\NewAdmin\AdminUserRequest;
use App\Models\User;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class AdminUserController extends Controller
{
    public function store(AdminUserRequest $request)
    {
        $data = $request->validated();
        [$firstName, $lastName] = $this->splitName($data['name']);

        $user = User::create([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $data['email'],
            'password' => Str::password(16),
            'type' => 'admin',
            'scope' => $data['scope'] ?? null,
            'two_factor_enabled' => $data['two_factor_enabled'] ?? false,
            'status' => $data['status'] === 'Active',
        ]);

        $role = Role::query()->where('guard_name', 'web')->where('name', $data['role'])->firstOrFail();
        $user->syncRoles([$role->name]);

        return redirect()->back()->with('message', 'Admin user created successfully.');
    }

    public function update(AdminUserRequest $request, User $adminUser)
    {
        abort_unless($adminUser->type === 'admin', 404);

        $data = $request->validated();

        if ($request->user()->is($adminUser) && $data['status'] !== 'Active') {
            return back()->with('error', 'You cannot suspend your own administrator account.');
        }

        if ($request->user()->is($adminUser) && ! $adminUser->hasRole($data['role'])) {
            return back()->with('error', 'You cannot change your own administrator role.');
        }

        [$firstName, $lastName] = $this->splitName($data['name']);

        $adminUser->update([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $data['email'],
            'scope' => $data['scope'] ?? null,
            'two_factor_enabled' => $data['two_factor_enabled'] ?? false,
            'status' => $data['status'] === 'Active',
        ]);

        $role = Role::query()->where('guard_name', 'web')->where('name', $data['role'])->firstOrFail();
        $adminUser->syncRoles([$role->name]);

        return redirect()->back()->with('message', 'Admin user updated successfully.');
    }

    public function destroy(User $adminUser)
    {
        abort_unless($adminUser->type === 'admin', 404);

        if (request()->user()->is($adminUser)) {
            return back()->with('error', 'You cannot delete your own administrator account.');
        }

        if ($adminUser->status && User::query()->where('type', 'admin')->where('status', true)->count() <= 1) {
            return back()->with('error', 'The last active administrator cannot be deleted.');
        }

        $adminUser->delete();

        return redirect()->back()->with('message', 'Admin user deleted successfully.');
    }

    private function splitName(string $name): array
    {
        $parts = explode(' ', trim($name), 2);

        return [$parts[0], $parts[1] ?? ''];
    }
}
