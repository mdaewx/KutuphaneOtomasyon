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
        // Role sütunu zaten 2025_03_22_103552_add_user_fields migration'ında eklendi
        // Bu migration dosyası artık gereksiz
        if (!Schema::hasColumn('users', 'role')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('role')->default('user')->after('is_admin');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Role sütununu silmiyoruz çünkü başka bir migration tarafından yönetiliyor
    }
};
