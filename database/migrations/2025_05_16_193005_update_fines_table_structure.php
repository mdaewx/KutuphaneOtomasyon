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
            // Add borrowing_id if it doesn't exist
            if (!Schema::hasColumn('fines', 'borrowing_id')) {
                $table->foreignId('borrowing_id')->nullable()->after('book_id');
            }
            
            // Add paid_at timestamp if it doesn't exist
            if (!Schema::hasColumn('fines', 'paid_at')) {
                $table->timestamp('paid_at')->nullable()->after('paid');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fines', function (Blueprint $table) {
            // Remove paid_at column if it exists
            if (Schema::hasColumn('fines', 'paid_at')) {
                $table->dropColumn('paid_at');
            }
            
            // Drop borrowing_id column if it exists
            if (Schema::hasColumn('fines', 'borrowing_id')) {
                $table->dropColumn('borrowing_id');
            }
        });
    }
};
