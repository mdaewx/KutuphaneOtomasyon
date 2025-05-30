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
        DB::table('borrowings')
            ->whereNull('fine_amount')
            ->update(['fine_amount' => 0.00]);

        // Sonra sütunu güncelle
        Schema::table('borrowings', function (Blueprint $table) {
            $table->decimal('fine_amount', 10, 2)->default(0.00)->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('borrowings', function (Blueprint $table) {
            $table->decimal('fine_amount', 10, 2)->nullable()->change();
        });
    }
}; 