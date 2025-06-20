<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('stocks', 'acquisition_date')) {
            Schema::table('stocks', function (Blueprint $table) {
                $table->date('acquisition_date')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('stocks', 'acquisition_date')) {
            Schema::table('stocks', function (Blueprint $table) {
                $table->dropColumn('acquisition_date');
            });
        }
    }
};
