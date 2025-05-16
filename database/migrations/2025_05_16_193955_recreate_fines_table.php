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
        // First save existing fines data
        $fines = [];
        if (Schema::hasTable('fines')) {
            $fines = DB::table('fines')->get()->toArray();
            
            // Drop the existing table
            Schema::dropIfExists('fines');
        }

        // Create new table with correct structure
        Schema::create('fines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('book_id')->constrained()->onDelete('cascade');
            $table->foreignId('borrowing_id')->nullable()->constrained()->onDelete('cascade');
            $table->integer('days_late')->nullable();
            $table->decimal('fine_amount', 5, 2)->default(0);
            $table->boolean('paid')->default(false);
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });

        // Restore the saved data if possible
        foreach ($fines as $fine) {
            $fineData = (array) $fine;
            
            // Ensure data matches new schema
            if (!isset($fineData['borrowing_id'])) {
                $fineData['borrowing_id'] = null;
            }
            
            if (!isset($fineData['paid_at'])) {
                $fineData['paid_at'] = null;
            }
            
            // Convert enum to boolean if needed
            if (isset($fineData['paid']) && is_string($fineData['paid'])) {
                $fineData['paid'] = ($fineData['paid'] === 'evet');
            }
            
            // Use insert since the original timestamps should be preserved
            DB::table('fines')->insert($fineData);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fines');
    }
};
