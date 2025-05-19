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
        Schema::table('fines', function (Blueprint $table) {
            $table->string('payment_method')->nullable()->after('paid_at');
            $table->text('payment_notes')->nullable()->after('payment_method');
            $table->foreignId('collected_by')->nullable()->after('payment_notes')
                  ->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fines', function (Blueprint $table) {
            $table->dropForeign(['collected_by']);
            $table->dropColumn(['payment_method', 'payment_notes', 'collected_by']);
        });
    }
};
