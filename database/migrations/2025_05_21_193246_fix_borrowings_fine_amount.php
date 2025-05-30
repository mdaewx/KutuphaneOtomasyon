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
        // Önce mevcut NULL değerleri 0.00 yap
        DB::statement('UPDATE borrowings SET fine_amount = 0.00 WHERE fine_amount IS NULL');
        
        // Sonra sütunu güncelle
        DB::statement('ALTER TABLE borrowings MODIFY fine_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE borrowings MODIFY fine_amount DECIMAL(10,2) NULL');
    }
}; 