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
        Schema::table('shelves', function (Blueprint $table) {
            // Add 'description' if it doesn't exist
            if (!Schema::hasColumn('shelves', 'description')) {
                $table->text('description')->nullable()->after('name');
            }
            
            // Add 'capacity' if it doesn't exist
            if (!Schema::hasColumn('shelves', 'capacity')) {
                $table->integer('capacity')->default(0)->after('description');
            }
            
            // Add 'location' if it doesn't exist
            if (!Schema::hasColumn('shelves', 'location')) {
                $table->string('location', 100)->nullable()->after('capacity');
            }
            
            // Add 'status' if it doesn't exist
            if (!Schema::hasColumn('shelves', 'status')) {
                $table->enum('status', ['active', 'inactive', 'maintenance'])->default('active')->after('location');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shelves', function (Blueprint $table) {
            if (Schema::hasColumn('shelves', 'description')) {
                $table->dropColumn('description');
            }
            if (Schema::hasColumn('shelves', 'capacity')) {
                $table->dropColumn('capacity');
            }
            if (Schema::hasColumn('shelves', 'location')) {
                $table->dropColumn('location');
            }
            if (Schema::hasColumn('shelves', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};
