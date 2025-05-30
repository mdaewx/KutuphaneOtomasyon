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
            // 1. Foreign key kontrolünü kapat
            DB::statement('SET FOREIGN_KEY_CHECKS=0');

            // 2. Eğer varsa yedek tabloyu sil
            DB::statement('DROP TABLE IF EXISTS borrowings_backup');

            // 3. Yedek tablo oluştur
            DB::unprepared('
                CREATE TABLE borrowings_backup LIKE borrowings;
                INSERT INTO borrowings_backup SELECT * FROM borrowings;
            ');

            // 4. Sütun var mı kontrol et ve sil
            $columnExists = DB::select("
                SELECT COUNT(*) as count 
                FROM information_schema.COLUMNS 
                WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = 'borrowings' 
                AND COLUMN_NAME = 'fine_amount'
            ");

            if ($columnExists[0]->count > 0) {
                DB::statement('ALTER TABLE borrowings DROP COLUMN fine_amount');
            }

            // 5. Yeni sütunu ekle
            DB::statement('ALTER TABLE borrowings ADD COLUMN fine_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00');

            // 6. Yedek tablodan verileri geri yükle
            DB::unprepared('
                UPDATE borrowings b1
                LEFT JOIN borrowings_backup b2 ON b1.id = b2.id
                SET b1.fine_amount = COALESCE(b2.fine_amount, 0.00);
            ');

            // 7. Yedek tabloyu sil
            DB::statement('DROP TABLE IF EXISTS borrowings_backup');

            // 8. Foreign key kontrolünü aç
            DB::statement('SET FOREIGN_KEY_CHECKS=1');

        } catch (\Exception $e) {
            // Hata durumunda foreign key kontrolünü geri aç ve yedek tabloyu temizle
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            DB::statement('DROP TABLE IF EXISTS borrowings_backup');
            throw $e;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Geri alma işlemi yok
    }
}; 