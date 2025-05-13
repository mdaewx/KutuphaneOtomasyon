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
            $table->string('book_title')->nullable();
            $table->text('author_ids')->nullable();
            $table->unsignedBigInteger('publisher_id')->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->string('language', 50)->nullable();
            $table->year('publication_year')->nullable();
            $table->text('description')->nullable();
            $table->string('shelf_number', 20)->nullable();

            // Add foreign key constraints
            $table->foreign('publisher_id')->references('id')->on('publishers')->onDelete('set null');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stocks', function (Blueprint $table) {
            $table->dropForeign(['publisher_id']);
            $table->dropForeign(['category_id']);
            
            $columns = [
                'book_title',
                'author_ids',
                'publisher_id',
                'category_id',
                'language',
                'publication_year',
                'description',
                'shelf_number'
            ];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('stocks', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
}; 