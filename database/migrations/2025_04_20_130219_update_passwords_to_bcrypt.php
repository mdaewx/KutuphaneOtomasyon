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
        // Varsayılan şifre: "password"
        $defaultBcryptPassword = Hash::make('password');
        
        DB::table('users')->update([
            'password' => $defaultBcryptPassword
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Geri alma işlemi için varsayılan MD5 şifre
        $defaultMd5Password = md5('password');
        
        DB::table('users')->update([
            'password' => $defaultMd5Password
        ]);
    }
};
