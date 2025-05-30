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
        // Eğer status sütunu varsa ve string tipindeyse
        if (Schema::hasColumn('fines', 'status')) {
            // Önce geçici bir sütun oluşturalım
            Schema::table('fines', function (Blueprint $table) {
                $table->boolean('status_new')->default(false);
            });

            // Mevcut değerleri yeni sütuna aktaralım
            DB::table('fines')
                ->update([
                    'status_new' => DB::raw("CASE WHEN status = 'paid' THEN 1 ELSE 0 END")
                ]);

            // Eski sütunu kaldırıp yeni sütunu yeniden adlandıralım
            Schema::table('fines', function (Blueprint $table) {
                $table->dropColumn('status');
            });

            Schema::table('fines', function (Blueprint $table) {
                $table->renameColumn('status_new', 'status');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('fines', 'status')) {
            // Önce geçici bir sütun oluşturalım
            Schema::table('fines', function (Blueprint $table) {
                $table->string('status_old')->default('pending');
            });

            // Mevcut değerleri yeni sütuna aktaralım
            DB::table('fines')
                ->update([
                    'status_old' => DB::raw("CASE WHEN status = 1 THEN 'paid' ELSE 'pending' END")
                ]);

            // Eski sütunu kaldırıp yeni sütunu yeniden adlandıralım
            Schema::table('fines', function (Blueprint $table) {
                $table->dropColumn('status');
            });

            Schema::table('fines', function (Blueprint $table) {
                $table->renameColumn('status_old', 'status');
            });
        }
    }
};
