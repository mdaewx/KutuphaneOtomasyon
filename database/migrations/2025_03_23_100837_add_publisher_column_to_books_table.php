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
            // Veritabanında publisher sütunu yoksa ekle
            if (!Schema::hasColumn('books', 'publisher')) {
                $table->string('publisher')->nullable()->after('category_id');
            }
            
            // Diğer eksik olabilecek sütunları da kontrol edelim
            if (!Schema::hasColumn('books', 'available_quantity')) {
                $table->integer('available_quantity')->default(0)->after('quantity');
            }
            
            // Kategori alanı ilişkisel olabilir
            if (Schema::hasColumn('books', 'category') && !Schema::hasColumn('books', 'category_id')) {
                $table->foreignId('category_id')->nullable()->after('description');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            // Migration'ı geri almak için sütunları kaldıralım
            if (Schema::hasColumn('books', 'publisher')) {
                $table->dropColumn('publisher');
            }
            
            // Diğer eklediğimiz sütunları da geri almak istiyorsak:
            // $table->dropColumn('available_quantity');
        });
    }
};
