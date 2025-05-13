<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Get all users
        $users = DB::table('users')->get();

        // Update each user's password with bcrypt hash
        foreach ($users as $user) {
            // Only update if the password is not already bcrypt hashed
            if (!$this->isBcrypt($user->password)) {
                DB::table('users')
                    ->where('id', $user->id)
                    ->update([
                        'password' => Hash::make('123456') // Temporary password
                    ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Cannot reverse this migration as we can't decrypt bcrypt hashes
    }

    /**
     * Check if a hash is a bcrypt hash
     */
    private function isBcrypt($hash)
    {
        return str_starts_with($hash, '$2y$');
    }
}; 