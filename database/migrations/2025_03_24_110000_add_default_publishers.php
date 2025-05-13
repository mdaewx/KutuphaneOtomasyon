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
        // Varsayılan yayınevlerini ekleyelim
        $publishers = [
            ['name' => 'Yapı Kredi Yayınları'],
            ['name' => 'İş Bankası Kültür Yayınları'],
            ['name' => 'Can Yayınları'],
            ['name' => 'Doğan Kitap'],
            ['name' => 'İletişim Yayınları'],
        ];

        foreach ($publishers as $publisher) {
            // Aynı isimde yayınevi yoksa ekle
            if (!DB::table('publishers')->where('name', $publisher['name'])->exists()) {
                DB::table('publishers')->insert(array_merge($publisher, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }

        // Kitapların publisher_id'lerini kontrol et ve null olanları varsayılan yayınevine ata
        $defaultPublisher = DB::table('publishers')->first();
        if ($defaultPublisher) {
            DB::table('books')
                ->whereNull('publisher_id')
                ->update(['publisher_id' => $defaultPublisher->id]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Bu migration geri alınabilir ancak veri kaybına neden olabilir
        // Bu nedenle down metodu boş bırakılmıştır
    }
}; 