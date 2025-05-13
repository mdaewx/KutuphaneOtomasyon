<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('acquisition_source_types', function (Blueprint $table) {
            $table->id();
            $table->string('type')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Varsayılan değerleri ekle
        DB::table('acquisition_source_types')->insert([
            [
                'type' => 'purchase',
                'name' => 'Satın Alma',
                'description' => 'Kitapların satın alma yoluyla edinilmesi',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'type' => 'donation',
                'name' => 'Bağış',
                'description' => 'Kitapların bağış yoluyla edinilmesi',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'type' => 'exchange',
                'name' => 'Değişim',
                'description' => 'Kitapların değişim yoluyla edinilmesi',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'type' => 'other',
                'name' => 'Diğer',
                'description' => 'Diğer edinme yöntemleri',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('acquisition_source_types');
    }
}; 