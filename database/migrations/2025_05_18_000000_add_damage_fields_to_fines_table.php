<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('fines', function (Blueprint $table) {
            $table->boolean('is_damage_fine')->default(false);
            $table->enum('damage_level', ['minor', 'moderate', 'severe'])->nullable();
            $table->text('damage_description')->nullable();
            $table->json('damage_photos')->nullable();
        });
    }

    public function down()
    {
        Schema::table('fines', function (Blueprint $table) {
            $table->dropColumn([
                'is_damage_fine',
                'damage_level',
                'damage_description',
                'damage_photos'
            ]);
        });
    }
}; 