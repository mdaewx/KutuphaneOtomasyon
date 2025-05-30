<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        try {
            // 1. Mevcut NULL değerleri 0.00'a çevir
            DB::statement('UPDATE borrowings SET fine_amount = 0.00 WHERE fine_amount IS NULL');
            
            // 2. Sütunu NOT NULL ve DEFAULT 0.00 olarak ayarla
            DB::statement('ALTER TABLE borrowings MODIFY COLUMN fine_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00');
            
        } catch (\Exception $e) {
            // Eğer sütun yoksa oluştur
            if (strpos($e->getMessage(), "Unknown column 'fine_amount'") !== false) {
                DB::statement('ALTER TABLE borrowings ADD COLUMN fine_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00');
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Hiçbir şey yapma, güvenli geri alma için
    }
}; 