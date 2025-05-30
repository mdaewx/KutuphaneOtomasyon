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
        Schema::table('stocks', function (Blueprint $table) {
            if (!Schema::hasColumn('stocks', 'barcode')) {
                $table->string('barcode')->nullable();
            }
            if (!Schema::hasColumn('stocks', 'status')) {
                $table->string('status')->default('available');
            }
            if (!Schema::hasColumn('stocks', 'condition')) {
                $table->enum('condition', ['new', 'good', 'fair', 'poor'])->default('new');
            }
            if (!Schema::hasColumn('stocks', 'is_available')) {
                $table->boolean('is_available')->default(true);
            }
            if (!Schema::hasColumn('stocks', 'shelf_id')) {
                $table->foreignId('shelf_id')->nullable()->constrained()->onDelete('set null');
            }
            if (!Schema::hasColumn('stocks', 'acquisition_source_id')) {
                $table->foreignId('acquisition_source_id')->nullable()->constrained()->onDelete('set null');
            }
            if (!Schema::hasColumn('stocks', 'acquisition_date')) {
                $table->date('acquisition_date')->nullable();
            }
            if (!Schema::hasColumn('stocks', 'acquisition_price')) {
                $table->decimal('acquisition_price', 10, 2)->nullable();
            }
            if (!Schema::hasColumn('stocks', 'notes')) {
                $table->text('notes')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stocks', function (Blueprint $table) {
            if (Schema::hasColumn('stocks', 'barcode')) {
                $table->dropColumn('barcode');
            }
            if (Schema::hasColumn('stocks', 'status')) {
                $table->dropColumn('status');
            }
            if (Schema::hasColumn('stocks', 'condition')) {
                $table->dropColumn('condition');
            }
            if (Schema::hasColumn('stocks', 'is_available')) {
                $table->dropColumn('is_available');
            }
            if (Schema::hasColumn('stocks', 'shelf_id')) {
                $table->dropForeign(['shelf_id']);
                $table->dropColumn('shelf_id');
            }
            if (Schema::hasColumn('stocks', 'acquisition_source_id')) {
                $table->dropForeign(['acquisition_source_id']);
                $table->dropColumn('acquisition_source_id');
            }
            if (Schema::hasColumn('stocks', 'acquisition_date')) {
                $table->dropColumn('acquisition_date');
            }
            if (Schema::hasColumn('stocks', 'acquisition_price')) {
                $table->dropColumn('acquisition_price');
            }
            if (Schema::hasColumn('stocks', 'notes')) {
                $table->dropColumn('notes');
            }
        });
    }
};
