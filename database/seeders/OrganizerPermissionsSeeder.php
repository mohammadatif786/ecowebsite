<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class OrganizerPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            'organizer dashboard',
            'organizer profile',
            'organizer events',
            'organizer sponsors',
            'organizer coupons',
            'organizer scan ticket',

            'organizer scanners',
            'organizer scanner assign role',
            'organizer scanner delete',
            'organizer scanner create',
            'organizer scanner store',
            'organizer scanner edit',
            'organizer scanner update',
            'organizer change status',
            
            'organizer scanner app setting',
            'organizer pos',
            'organizer reviews',
            'organizer payouts',
            'organizer payout methods',
            'organizer reports',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => trim($permission), 'guard_name' => 'web']);
        }

        $allPermissions = Permission::whereIn('name', $permissions)->get();

        $organizerRole = Role::where('name', 'organizer')->first();
        if ($organizerRole) {
            $organizerRole->syncPermissions($allPermissions);
        }

        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $adminRole->givePermissionTo($allPermissions);
        }

        // Scanner Guard Permissions
        $scannerPermissions = [
            'scanner scan ticket',
            'scanner view attendees',
            'scanner pos access',
        ];

        foreach ($scannerPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'scanner']);
        }
    }
}
