<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    public function up(): void
    {
        // Reset all user passwords to '123456'
        DB::table('users')->update([
            'password' => Hash::make('123456')
        ]);
    }

    public function down(): void
    {
        // Cannot reverse this migration as we can't decrypt bcrypt hashes
    }
}; 