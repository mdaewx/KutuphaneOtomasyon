<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('user');
        });

        // Mevcut kullanıcıların rollerini güncelle
        User::where('is_admin', 1)->update(['role' => 'admin']);
        User::where('is_staff', 1)->where('is_admin', 0)->update(['role' => 'staff']);
        User::where('is_staff', 0)->where('is_admin', 0)->update(['role' => 'user']);
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
}; 