<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $permissions = [
            'view dashboard',

            'view users',
            'view flagged users',
            'view kyc users',

            'view events',
            'view categories',
            'view ticket sales',
            'view sponsors',
            'view coupons',

            'view event organizers',
            'view scanners',

            'view wallet dashboard',
            'view digi money',
            'view transactions',
            'view wallet settings',
            'view payment methods',

            'view ads',
            'view ads dashboard',
            'view ads analytics',
            'view ads reports',
            'view all ads list',
            'view restaurants ads',
            'view clubs and fetes ads',
            'view ads campaigns',
            'view ads email sponsor',
            'view ads c360 news',

            'view restaurants',
            'view clubs',

            'view swipes',

            'view user plans',
            'view subscribed users',

            'view dashboard',
            'view messages',
            'view encounters',
            'view calls',
            'view live streams',
            'view gifts',
            'view payments',
            'view payouts',
            'view fee analytics',
            'view live analytics',
            'view moderation',

            'view products',
            'view product categories',
            'view orders',
            'fees',
            'view escrow releases',
            'view seller transfers',
            'view wallets',
            'view sellers (Store/Group)',

            'view reports',

            'view push notifications',
            'view emails',
            'send emails',
            'manage email templates',

            'view taxes',
            'taxes dashboard',
            'taxes setting',
            'taxes remittance setting',
            'taxes remittance center',

            'view news',
            'create news',
            'edit news',
            'delete news',
            'toggle news',

            'view admins',
            'view roles',

            'view app settings',
            'view ads settings',
            'view smtp settings',
            'view wallet',
            'view events analytics',
            'view moderation',

            'view wallet kyc',

            'subscribed users',
            'subscription index',
            'subscription dashboard',
            'subscription create',
            'subscription delete',
            'subscription update',

            'feature gate index',
            'feature gate delete',
            'feature gate update',
            'feature gate store',


        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $user = Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);
        $organizer = Role::firstOrCreate(['name' => 'organizer', 'guard_name' => 'web']);
        $scannerGuardRole = Role::firstOrCreate(['name' => 'scanner', 'guard_name' => 'scanner']);

        $admin->syncPermissions(Permission::where('guard_name', 'web')->get());
    }
}
