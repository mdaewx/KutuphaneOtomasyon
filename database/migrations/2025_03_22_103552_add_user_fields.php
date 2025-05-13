<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('surname', 50)->nullable()->after('name');
            $table->string('phone', 15)->nullable()->after('email');
            $table->text('address')->nullable()->after('phone');
            $table->enum('role', ['admin', 'user'])->default('user')->after('address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['surname', 'phone', 'address', 'role']);
        });
    }
};
