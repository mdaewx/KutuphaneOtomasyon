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
        Schema::table('fines', function (Blueprint $table) {
            // Önce mevcut status sütununu yedekleyelim
            $table->renameColumn('status', 'status_old');
        });

        Schema::table('fines', function (Blueprint $table) {
            // Yeni boolean status sütunu ekleyelim
            $table->boolean('status')->default(false);
        });

        // Eski değerleri yeni sütuna aktaralım
        DB::statement("UPDATE fines SET status = CASE WHEN status_old = 'paid' THEN 1 ELSE 0 END");

        Schema::table('fines', function (Blueprint $table) {
            // Eski sütunu silelim
            $table->dropColumn('status_old');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fines', function (Blueprint $table) {
            // Önce mevcut boolean status sütununu yedekleyelim
            $table->renameColumn('status', 'status_old');
        });

        Schema::table('fines', function (Blueprint $table) {
            // String status sütununu geri ekleyelim
            $table->string('status')->default('pending');
        });

        // Eski değerleri geri aktaralım
        DB::statement("UPDATE fines SET status = CASE WHEN status_old = 1 THEN 'paid' ELSE 'pending' END");

        Schema::table('fines', function (Blueprint $table) {
            // Eski sütunu silelim
            $table->dropColumn('status_old');
        });
    }
};
