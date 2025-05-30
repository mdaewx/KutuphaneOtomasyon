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
        Schema::table('borrowings', function (Blueprint $table) {
            $table->string('status')->default('pending')->after('id');
        });

        // Update existing records to have 'active' status
        DB::table('borrowings')
            ->whereNull('returned_at')
            ->update(['status' => 'active']);

        // Update returned records
        DB::table('borrowings')
            ->whereNotNull('returned_at')
            ->update(['status' => 'returned']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('borrowings', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
