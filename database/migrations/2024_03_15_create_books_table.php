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
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('isbn')->unique()->nullable();
            $table->text('description')->nullable();
            $table->foreignId('publisher_id')->nullable()->constrained()->onDelete('set null');
            $table->integer('publication_year')->nullable();
            $table->string('language')->default('Türkçe');
            $table->string('cover_image')->nullable();
            $table->integer('page_count')->nullable();
            $table->string('status')->default('available');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('books');
    }
}; 