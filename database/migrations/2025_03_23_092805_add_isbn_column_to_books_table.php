<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Bu kolonlar zaten başka bir migration içinde eklenmiş olabilir
        // ISBN kolonunu ekle
        if (!Schema::hasColumn('books', 'isbn')) {
            Schema::table('books', function (Blueprint $table) {
                $table->string('isbn')->nullable()->unique()->after('page_count');
            });
        }
        
        // Language kolonunu ekle
        if (!Schema::hasColumn('books', 'language')) {
            Schema::table('books', function (Blueprint $table) {
                $table->string('language')->nullable()->after('publisher_id')->default('Türkçe');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            if (Schema::hasColumn('books', 'isbn')) {
                $table->dropColumn('isbn');
            }
            if (Schema::hasColumn('books', 'language')) {
                $table->dropColumn('language');
            }
        });
    }
};
