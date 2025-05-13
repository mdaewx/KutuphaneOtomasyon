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
        Schema::table('shelves', function (Blueprint $table) {
            // Önce code sütununu name olarak yeniden adlandır
            if (Schema::hasColumn('shelves', 'code') && !Schema::hasColumn('shelves', 'name')) {
                $table->renameColumn('code', 'name');
            }
            // Eğer code ve name sütunları yoksa name sütununu ekle
            else if (!Schema::hasColumn('shelves', 'code') && !Schema::hasColumn('shelves', 'name')) {
                $table->string('name')->unique()->after('id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shelves', function (Blueprint $table) {
            if (Schema::hasColumn('shelves', 'name')) {
                $table->renameColumn('name', 'code');
            }
        });
    }
}; 