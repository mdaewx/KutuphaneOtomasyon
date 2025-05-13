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
            // Add language column if it doesn't exist
            if (!Schema::hasColumn('books', 'language')) {
                $table->string('language')->nullable()->after('description');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            // Drop language column if it exists
            if (Schema::hasColumn('books', 'language')) {
                $table->dropColumn('language');
            }
        });
    }
};
