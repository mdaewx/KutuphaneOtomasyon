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
        Schema::table('borrowings', function (Blueprint $table) {
            // Status field
            if (!Schema::hasColumn('borrowings', 'status')) {
                $table->enum('status', ['pending', 'approved', 'rejected', 'returned'])->default('pending')->after('returned_at');
            }
            
            // Reject reason field
            if (!Schema::hasColumn('borrowings', 'reject_reason')) {
                $table->string('reject_reason')->nullable()->after('status');
            }
            
            // Condition field
            if (!Schema::hasColumn('borrowings', 'condition')) {
                $table->enum('condition', ['good', 'damaged', 'lost'])->nullable()->after('reject_reason');
            }
            
            // Notes field
            if (!Schema::hasColumn('borrowings', 'notes')) {
                $table->text('notes')->nullable()->after('condition');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('borrowings', function (Blueprint $table) {
            if (Schema::hasColumn('borrowings', 'status')) {
                $table->dropColumn('status');
            }
            
            if (Schema::hasColumn('borrowings', 'reject_reason')) {
                $table->dropColumn('reject_reason');
            }
            
            if (Schema::hasColumn('borrowings', 'condition')) {
                $table->dropColumn('condition');
            }
            
            if (Schema::hasColumn('borrowings', 'notes')) {
                $table->dropColumn('notes');
            }
        });
    }
};
