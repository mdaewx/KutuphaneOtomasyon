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
            // Remove publisher string column if exists
            if (Schema::hasColumn('books', 'publisher')) {
                $table->dropColumn('publisher');
            }
            
            // Add publisher_id if not exists
            if (!Schema::hasColumn('books', 'publisher_id')) {
                $table->foreignId('publisher_id')->nullable()->after('category_id')->constrained();
            }
            
            // Remove isbn if exists
            if (Schema::hasColumn('books', 'isbn')) {
                $table->dropColumn('isbn');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            if (Schema::hasColumn('books', 'publisher_id')) {
                $table->dropForeign(['publisher_id']);
                $table->dropColumn('publisher_id');
            }
            
            if (!Schema::hasColumn('books', 'publisher')) {
                $table->string('publisher')->nullable();
            }
            
            if (!Schema::hasColumn('books', 'isbn')) {
                $table->string('isbn')->nullable();
            }
        });
    }
};
