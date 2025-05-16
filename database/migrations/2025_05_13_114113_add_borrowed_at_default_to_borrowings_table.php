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
        // borrowed_at sütununu NULL kabul edecek şekilde değiştiriyoruz
        DB::statement('ALTER TABLE borrowings MODIFY borrowed_at TIMESTAMP NULL DEFAULT NULL');
        
        // Eğer sütun yoksa yeni sütun ekliyoruz
        if (!Schema::hasColumn('borrowings', 'borrowed_at')) {
            DB::statement('ALTER TABLE borrowings ADD borrowed_at TIMESTAMP NULL DEFAULT NULL');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Geri alınacak bir şey yok
    }
};
