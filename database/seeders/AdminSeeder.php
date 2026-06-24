<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use BezhanSalleh\FilamentShield\Support\Utils;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // 1. buat role super admin
        $role = Role::firstOrCreate([
            'name' => 'super_admin'
        ]);

        // 2. buat user admin
        $user = User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
            ]
        );

        // 3. assign role
        $user->assignRole($role);

        // 4. sync semua permission (penting untuk Filament Shield)
        $role->syncPermissions(\Spatie\Permission\Models\Permission::all());
    }
}