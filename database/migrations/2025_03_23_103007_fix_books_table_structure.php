<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('books', function (Blueprint $table) {
            // Kontrol ederek eksik sütunları ekleyelim
            
            // slug sütunu
            if (!Schema::hasColumn('books', 'slug')) {
                $table->string('slug')->nullable()->after('title');
            }
            
            // publisher_id sütunu
            if (!Schema::hasColumn('books', 'publisher_id')) {
                $table->foreignId('publisher_id')->nullable()->after('category_id');
            }
            
            // isbn sütunu
            if (!Schema::hasColumn('books', 'isbn')) {
                $table->string('isbn')->nullable()->unique()->after('publication_year');
            }
            
            // shelf_number sütunu 
            if (!Schema::hasColumn('books', 'shelf_number')) {
                $table->string('shelf_number')->nullable()->after('page_count');
            }
            
            // language sütunu
            if (!Schema::hasColumn('books', 'language')) {
                $table->string('language')->nullable()->after('publisher_id');
            }
            
            // quantity sütunu
            if (!Schema::hasColumn('books', 'quantity')) {
                $table->integer('quantity')->default(1)->after('status');
            }
            
            // available_quantity sütunu
            if (!Schema::hasColumn('books', 'available_quantity')) {
                $table->integer('available_quantity')->default(1)->after('quantity');
            }
            
            // cover_image sütunu
            if (Schema::hasColumn('books', 'image_url') && !Schema::hasColumn('books', 'cover_image')) {
                $table->renameColumn('image_url', 'cover_image');
            } else if (!Schema::hasColumn('books', 'cover_image')) {
                $table->string('cover_image')->nullable()->after('available_quantity');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Bu migration için geri alma işlemi yapmıyoruz
        // Çünkü mevcut verilerin doğru biçimde korunması daha önemli
    }
};
