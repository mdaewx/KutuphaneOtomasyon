<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UpdateUserRolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Update all admin users
        User::where('is_admin', true)
            ->update(['role' => 'admin']);

        // Update all regular users (ensure they have the 'user' role)
        User::where('is_admin', false)
            ->update(['role' => 'user']);

        // Specifically find a user named 'irmak' and make them an admin
        User::where('name', 'like', '%irmak%')
            ->update([
                'role' => 'admin',
                'is_admin' => true
            ]);

        $this->command->info('All user roles have been updated successfully.');
        
        // Count admins
        $adminCount = User::where('role', 'admin')->count();
        $this->command->info("Total admin users: {$adminCount}");
        
        // List all admins
        $admins = User::where('role', 'admin')->get(['id', 'name', 'email']);
        $this->command->info('Admin users:');
        foreach ($admins as $admin) {
            $this->command->info("- ID: {$admin->id}, Name: {$admin->name}, Email: {$admin->email}");
        }
    }
}
