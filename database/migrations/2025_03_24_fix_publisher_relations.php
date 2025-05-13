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
        // Önce varsayılan bir yayınevi oluşturalım (eğer yoksa)
        $defaultPublisherId = DB::table('publishers')->insertGetId([
            'name' => 'Varsayılan Yayınevi',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // publisher_id'si null olan kitapları varsayılan yayınevine atayalım
        DB::table('books')
            ->whereNull('publisher_id')
            ->update(['publisher_id' => $defaultPublisherId]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Bu migration geri alınabilir ancak veri kaybı olabilir
        // Bu nedenle down metodu boş bırakılmıştır
    }
}; 