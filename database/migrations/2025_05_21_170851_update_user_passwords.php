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
        // Mevcut şifreleri güncelle
        $users = DB::table('users')->get();
        
        foreach ($users as $user) {
            // Eğer şifre 8 karakterden kısaysa, sonuna '12345678' ekle
            $password = $user->password;
            if (strlen($password) < 8) {
                $password = Hash::make('12345678');
                
                DB::table('users')
                    ->where('id', $user->id)
                    ->update(['password' => $password]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Bu migration geri alınamaz
    }
};
