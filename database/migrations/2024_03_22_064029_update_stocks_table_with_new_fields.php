<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('stocks', function (Blueprint $table) {
            // Önce mevcut sütunları kontrol et ve yoksa ekle
            if (!Schema::hasColumn('stocks', 'barcode')) {
                $table->string('barcode')->nullable()->unique();
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

    public function down()
    {
        Schema::table('stocks', function (Blueprint $table) {
            $table->dropColumn([
                'barcode',
                'status',
                'condition',
                'is_available',
                'acquisition_date',
                'acquisition_price',
                'notes'
            ]);
        });
    }
}; 