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
        // Borrowings tablosu zaten 2025_03_22_211919_create_borrowings_table.php migration'ında oluşturulduğu için
        // bu migration'ı boş bırakıyoruz. Lütfen önce o migration'ı çalıştırın.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Boş bırakıldı çünkü gerçek borrowings tablosu işlemi başka bir migration'da
    }
};
