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
        Schema::create('acquisition_sources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('source_type_id')->constrained('acquisition_source_types')->onDelete('restrict');
            $table->string('source_name');
            $table->decimal('price', 10, 2)->nullable();
            $table->date('acquisition_date');
            $table->integer('quantity')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('acquisition_sources');
    }
}; 