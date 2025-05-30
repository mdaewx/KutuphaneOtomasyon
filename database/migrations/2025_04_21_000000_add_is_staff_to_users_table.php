<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'is_staff')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('is_staff')->default(false)->after('is_admin');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'is_staff')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('is_staff');
            });
        }
    }
}; 