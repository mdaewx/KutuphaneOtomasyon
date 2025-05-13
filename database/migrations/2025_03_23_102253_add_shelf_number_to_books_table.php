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
        Schema::table('books', function (Blueprint $table) {
            // Veritabanında shelf_number sütunu yoksa ekle
            if (!Schema::hasColumn('books', 'shelf_number')) {
                $table->string('shelf_number')->nullable()->after('page_count');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            // Migration'ı geri almak için sütunu kaldır
            if (Schema::hasColumn('books', 'shelf_number')) {
                $table->dropColumn('shelf_number');
            }
        });
    }
};
