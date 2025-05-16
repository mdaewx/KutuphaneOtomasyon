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
        // Tüm stokları mevcut ve kullanılabilir olarak işaretleyelim
        DB::table('stocks')->update([
            'is_available' => true,
            'status' => 'available'
        ]);
        
        // Aktif ödünç verme kaydı olmayan tüm kitaplar için borrows tablosunu da kontrol edelim
        $activelyBorrowedBookIds = DB::table('borrowings')
            ->whereNull('returned_at')
            ->pluck('book_id')
            ->toArray();
            
        // Tüm kitapları mevcut olarak işaretleyip, sonra aktif ödünç kaydı olanları güncelleyelim
        DB::table('books')
            ->update([
                'available_quantity' => DB::raw('(SELECT COUNT(*) FROM stocks WHERE stocks.book_id = books.id)')
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Geriye dönme gerekmiyor, mevcut durumu koruyacağız
    }
};
