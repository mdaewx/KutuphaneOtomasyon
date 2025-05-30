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
        if (!Schema::hasColumn('fines', 'amount')) {
            Schema::table('fines', function (Blueprint $table) {
                $table->decimal('amount', 10, 2)->default(0);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('fines', 'amount')) {
            Schema::table('fines', function (Blueprint $table) {
                $table->dropColumn('amount');
            });
        }
    }
};
