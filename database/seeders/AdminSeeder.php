<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run()
    {
        $adminRole = Role::where('slug', 'admin')->first();
        $staffRole = Role::where('slug', 'staff')->first();

        // Admin kullanıcısını oluştur veya güncelle
        $admin = User::updateOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('admin123'),
                'is_admin' => true,
            ]
        );

        // Memur kullanıcısını oluştur veya güncelle
        $staff = User::updateOrCreate(
            ['email' => 'staff@staff.com'],
            [
                'name' => 'Staff User',
                'password' => Hash::make('staff123'),
                'is_staff' => true,
            ]
        );

        // Admin rolünü atama
        $admin->roles()->sync($adminRole->id);

        // Staff rolünü atama
        $staff->roles()->sync($staffRole->id);
    }
} 