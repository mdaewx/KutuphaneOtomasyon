<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('acquisition_sources', function (Blueprint $table) {
            // Var olan sütunları kontrol et ve eksik olanları ekle
            if (!Schema::hasColumn('acquisition_sources', 'source_name')) {
                $table->string('source_name')->after('source_type_id');
            }
            if (!Schema::hasColumn('acquisition_sources', 'price')) {
                $table->decimal('price', 10, 2)->nullable()->after('source_name');
            }
            if (!Schema::hasColumn('acquisition_sources', 'acquisition_date')) {
                $table->date('acquisition_date')->after('price');
            }
            if (!Schema::hasColumn('acquisition_sources', 'notes')) {
                $table->text('notes')->nullable()->after('acquisition_date');
            }
            if (!Schema::hasColumn('acquisition_sources', 'quantity')) {
                $table->integer('quantity')->default(1)->after('book_id');
            }
        });
    }

    public function down()
    {
        Schema::table('acquisition_sources', function (Blueprint $table) {
            $table->dropColumn(['source_name', 'price', 'acquisition_date', 'notes', 'quantity']);
        });
    }
}; 