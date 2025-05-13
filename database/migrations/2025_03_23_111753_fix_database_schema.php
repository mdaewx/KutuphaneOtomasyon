<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Books tablosunu tamamen düzeltiyoruz
        if (Schema::hasTable('books')) {
            // 1. Önce string olarak tanımlı 'category' alanını kontrol et
            // Eğer 'category' alanı varsa ve 'category_id' yoksa, önce foreign key oluştur 
            if (Schema::hasColumn('books', 'category') && !Schema::hasColumn('books', 'category_id')) {
                Schema::table('books', function (Blueprint $table) {
                    $table->foreignId('category_id')->nullable()->after('description');
                });
                
                // Burada category_id için güvenli bir değer ata
                $defaultCategoryId = DB::table('categories')->insertGetId([
                    'name' => 'Genel',
                    'description' => 'Otomatik oluşturulmuş varsayılan kategori',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                // Tüm kitaplar için bu kategoriyi kullan
                DB::table('books')->update(['category_id' => $defaultCategoryId]);
                
                // Artık string category alanını kaldırabiliriz
                Schema::table('books', function (Blueprint $table) {
                    $table->dropColumn('category');
                });
            }
            
            // 2. Gerekli tüm sütunları ekleyelim (yoksa)
            Schema::table('books', function (Blueprint $table) {
                // Slug alanı
                if (!Schema::hasColumn('books', 'slug')) {
                    $table->string('slug')->nullable()->after('title');
                    
                    // Mevcut kitaplara slug değeri ata
                    DB::statement('UPDATE books SET slug = LOWER(REPLACE(REPLACE(REPLACE(title, " ", "-"), ".", ""), ",", ""))');
                }
                
                // Publisher_id alanı
                if (!Schema::hasColumn('books', 'publisher_id')) {
                    $table->foreignId('publisher_id')->nullable()->after('category_id');
                    
                    // Varsayılan publisher oluştur
                    $defaultPublisherId = DB::table('publishers')->insertGetId([
                        'name' => 'Varsayılan Yayınevi',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    
                    // Tüm kitaplar için bu yayınevini kullan
                    DB::table('books')->update(['publisher_id' => $defaultPublisherId]);
                }
                
                // Language alanı
                if (!Schema::hasColumn('books', 'language')) {
                    $table->string('language')->nullable()->after('publisher_id');
                    
                    // Varsayılan dil olarak Türkçe ata
                    DB::table('books')->update(['language' => 'Türkçe']);
                }
                
                // ISBN alanı - önemli: eğer bu alan kaldırıldıysa geri ekle
                if (!Schema::hasColumn('books', 'isbn')) {
                    $table->string('isbn')->nullable()->unique()->after('publication_year');
                }
                
                // Shelf number alanı
                if (!Schema::hasColumn('books', 'shelf_number')) {
                    $table->string('shelf_number')->nullable()->after('page_count');
                }
                
                // Quantity alanı
                if (!Schema::hasColumn('books', 'quantity')) {
                    $table->integer('quantity')->default(1)->after('status');
                }
                
                // Available quantity alanı
                if (!Schema::hasColumn('books', 'available_quantity')) {
                    $table->integer('available_quantity')->default(1)->after('quantity');
                }
                
                // Cover_image alanı
                if (Schema::hasColumn('books', 'image_url') && !Schema::hasColumn('books', 'cover_image')) {
                    $table->renameColumn('image_url', 'cover_image');
                } else if (!Schema::hasColumn('books', 'cover_image')) {
                    $table->string('cover_image')->nullable()->after('available_quantity');
                }
            });
        }
        
        // Categories tablosunu kontrol et
        if (!Schema::hasTable('categories')) {
            Schema::create('categories', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->text('description')->nullable();
                $table->string('color')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
            
            // Varsayılan kategoriler ekle
            DB::table('categories')->insert([
                ['name' => 'Roman', 'description' => 'Romanlar', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Tarih', 'description' => 'Tarih kitapları', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Bilim', 'description' => 'Bilim kitapları', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Felsefe', 'description' => 'Felsefe kitapları', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Psikoloji', 'description' => 'Psikoloji kitapları', 'created_at' => now(), 'updated_at' => now()],
            ]);
        }
        
        // Publishers tablosu
        if (!Schema::hasTable('publishers')) {
            Schema::create('publishers', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('address')->nullable();
                $table->string('phone')->nullable();
                $table->string('email')->nullable();
                $table->string('website')->nullable();
                $table->text('description')->nullable();
                $table->string('logo')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
            
            // Varsayılan yayınevleri ekle
            DB::table('publishers')->insert([
                ['name' => 'Yapı Kredi Yayınları', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'İş Bankası Kültür Yayınları', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Can Yayınları', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Doğan Kitap', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'İletişim Yayınları', 'created_at' => now(), 'updated_at' => now()],
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Bu migration geri alınmaz
        // Çünkü veri kaybına neden olabilir
    }
};
