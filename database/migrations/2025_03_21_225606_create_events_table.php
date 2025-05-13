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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->date('date');
            $table->string('location', 255);
            // Only add foreign keys if the tables exist
            if (Schema::hasTable('categories')) {
                $table->foreignId('category_id')->nullable()->constrained();
            } else {
                $table->unsignedBigInteger('category_id')->nullable();
            }
            if (Schema::hasTable('publishers')) {
                $table->foreignId('publisher_id')->nullable()->constrained();
            } else {
                $table->unsignedBigInteger('publisher_id')->nullable();
            }
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
