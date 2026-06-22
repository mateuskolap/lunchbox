<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'users.index',
            'users.store',
            'users.update',
            'users.destroy',
            'roles.index',
            'roles.store',
            'roles.update',
            'roles.destroy',
            'roles.add',
            'roles.remove',
            'products.index',
            'products.store',
            'products.update',
            'products.destroy',
            'customers.index',
            'customers.store',
            'customers.update',
            'customers.destroy',
            'orders.index',
            'orders.store',
            'orders.update',
            'payments.index',
            'payments.store',
        ];

        Permission::whereNotIn('name', $permissions)->delete();

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
            ]);
        }
    }
}
