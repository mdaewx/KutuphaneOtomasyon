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
        // Tüm kullanıcıların şifrelerini "password123" olarak ayarla
        DB::table('users')->update([
            'password' => Hash::make('password123')
        ]);

        // Özel olarak admin kullanıcısının şifresini ayarla
        DB::table('users')
            ->where('email', 'hatice@irmak.com')
            ->update([
                'password' => Hash::make('admin123456')
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Bu migration geri alınamaz
    }
};
