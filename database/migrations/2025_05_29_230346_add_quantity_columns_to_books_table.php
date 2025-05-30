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
        Schema::table('books', function (Blueprint $table) {
            if (Schema::hasColumn('books', 'quantity')) {
                $table->dropColumn('quantity');
            }
            if (Schema::hasColumn('books', 'available_quantity')) {
                $table->dropColumn('available_quantity');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->integer('quantity')->default(0)->after('id');
            $table->integer('available_quantity')->default(0)->after('quantity');
        });
    }
};
