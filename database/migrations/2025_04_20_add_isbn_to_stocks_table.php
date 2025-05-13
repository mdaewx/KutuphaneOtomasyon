<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::table('stocks', function (Blueprint $table) {
            if (!Schema::hasColumn('stocks', 'isbn')) {
                $table->string('isbn')->nullable();
            }
        });

        // Mevcut stoklar için ISBN bilgisini kitaplardan kopyala
        DB::statement('UPDATE stocks s 
            INNER JOIN books b ON s.book_id = b.id 
            SET s.isbn = b.isbn 
            WHERE s.isbn IS NULL');
    }

    public function down()
    {
        Schema::table('stocks', function (Blueprint $table) {
            if (Schema::hasColumn('stocks', 'isbn')) {
                $table->dropColumn('isbn');
            }
        });
    }
}; 