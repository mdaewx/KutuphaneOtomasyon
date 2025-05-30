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
        // Add code column if it doesn't exist
        if (!Schema::hasColumn('shelves', 'code')) {
            Schema::table('shelves', function (Blueprint $table) {
                $table->string('code')->nullable()->after('shelf_number');
            });

            // Update existing records with a code based on shelf_number
            $shelves = DB::table('shelves')->whereNull('code')->get();
            foreach ($shelves as $shelf) {
                DB::table('shelves')
                    ->where('id', $shelf->id)
                    ->update(['code' => $shelf->shelf_number ?? ('RAF-' . str_pad($shelf->id, 3, '0', STR_PAD_LEFT))]);
            }

            // Make code column non-nullable after updating existing records
            Schema::table('shelves', function (Blueprint $table) {
                $table->string('code')->nullable(false)->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('shelves', 'code')) {
            Schema::table('shelves', function (Blueprint $table) {
                $table->dropColumn('code');
            });
        }
    }
};
