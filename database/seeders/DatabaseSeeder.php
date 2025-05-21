<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            AdminSeeder::class,
            StaffSeeder::class,
            AcquisitionSourceTypeSeeder::class,
            AcquisitionSourceSeeder::class,
        ]);

        // Rolleri oluştur
        $adminRole = Role::firstOrCreate(
            ['slug' => 'admin'],
            ['name' => 'Admin']
        );

        $staffRole = Role::firstOrCreate(
            ['slug' => 'staff'],
            ['name' => 'Staff']
        );

        // Admin kullanıcısını güncelle veya oluştur
        $admin = User::updateOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Admin User',
                'password' => bcrypt('admin123'),
                'is_admin' => true,
            ]
        );

        // Staff kullanıcısını güncelle veya oluştur
        $staff = User::updateOrCreate(
            ['email' => 'staff@library.com'],
            [
                'name' => 'Kütüphane Memuru',
                'password' => bcrypt('password'),
                'is_staff' => true,
            ]
        );

        // Rolleri ata
        $admin->roles()->sync([$adminRole->id]);
        $staff->roles()->sync([$staffRole->id]);

        // Diğer seeder'ları çalıştır
        $this->call([
            CategorySeeder::class,
            PublisherSeeder::class,
            ShelfSeeder::class,
        ]);
    }
}
