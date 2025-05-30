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
        // Tüm stokları available olarak güncelle
        DB::table('stocks')->update([
            'status' => 'available',
            'is_available' => true
        ]);

        // Ödünç verilmiş stokları borrowed olarak güncelle
        DB::table('stocks')
            ->join('borrowings', 'stocks.id', '=', 'borrowings.stock_id')
            ->whereNull('borrowings.returned_at')
            ->update(['stocks.status' => 'borrowed', 'stocks.is_available' => false]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Bu migration geri alınırsa hiçbir şey yapma
    }
};
