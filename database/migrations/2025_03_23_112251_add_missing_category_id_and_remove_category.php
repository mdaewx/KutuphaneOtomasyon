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
        if (!Schema::hasTable('categories')) {
            // Categories tablosunu oluştur
            Schema::create('categories', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->text('description')->nullable();
                $table->timestamps();
            });

            // Varsayılan kategorileri ekle
            DB::table('categories')->insert([
                ['name' => 'Genel', 'description' => 'Genel kitaplar', 'created_at' => now(), 'updated_at' => now()]
            ]);
        }

        // Books tablosu düzeltmeleri
        if (Schema::hasTable('books')) {
            // Eğer category_id alanı yoksa, category alanındaki değerlerle birlikte ekle
            if (!Schema::hasColumn('books', 'category_id')) {
                Schema::table('books', function (Blueprint $table) {
                    $table->unsignedBigInteger('category_id')->nullable()->after('description');
                });

                // Varsayılan kategori ID'sini al
                $defaultCategoryId = DB::table('categories')->where('name', 'Genel')->value('id');
                if (!$defaultCategoryId) {
                    $defaultCategoryId = DB::table('categories')->insertGetId([
                        'name' => 'Genel',
                        'description' => 'Genel kitaplar',
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }

                // Tüm kitaplara varsayılan kategori ata
                DB::table('books')->update(['category_id' => $defaultCategoryId]);
            }

            // category alanı varsa ve kaldırılması gerekiyorsa
            if (Schema::hasColumn('books', 'category')) {
                Schema::table('books', function (Blueprint $table) {
                    $table->dropColumn('category');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Bu migration'ı geri almak veri kaybına yol açabileceği için
        // geri alma işlemi uygulanmıyor
    }
};
