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
        // Önce shelves tablosunun var olup olmadığını kontrol et
        if (!Schema::hasTable('shelves')) {
            Schema::create('shelves', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('shelf_number')->nullable();
                $table->text('description')->nullable();
                $table->integer('capacity')->default(50);
                $table->string('location')->nullable();
                $table->string('status')->default('active');
                $table->timestamps();
            });
        }
        
        // Eğer shelf_number sütunu yoksa ekle
        if (!Schema::hasColumn('shelves', 'shelf_number')) {
            Schema::table('shelves', function (Blueprint $table) {
                $table->string('shelf_number')->nullable()->after('name');
            });
        }

        // Boş raf numaralarını güncelle
        $shelves = DB::table('shelves')->whereNull('shelf_number')->get();
        foreach ($shelves as $shelf) {
            DB::table('shelves')
                ->where('id', $shelf->id)
                ->update(['shelf_number' => 'RAF-' . str_pad($shelf->id, 3, '0', STR_PAD_LEFT)]);
        }

        // Artık tüm raflarda numara olduğu için nullable false yap
        Schema::table('shelves', function (Blueprint $table) {
            $table->string('shelf_number')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shelves', function (Blueprint $table) {
            $table->string('shelf_number')->nullable()->change();
        });
    }
}; 