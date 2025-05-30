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
        Schema::table('acquisition_sources', function (Blueprint $table) {
            // Önce name alanını nullable yap
            $table->string('name')->nullable()->change();
            
            // Eğer source_name değeri varsa name alanına kopyala
            DB::statement('UPDATE acquisition_sources SET name = source_name WHERE source_name IS NOT NULL');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('acquisition_sources', function (Blueprint $table) {
            $table->string('name')->nullable(false)->change();
        });
    }
}; 