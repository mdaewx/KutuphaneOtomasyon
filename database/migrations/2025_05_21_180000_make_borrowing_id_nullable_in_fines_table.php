<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('fines', function (Blueprint $table) {
            $table->unsignedBigInteger('borrowing_id')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('fines', function (Blueprint $table) {
            $table->unsignedBigInteger('borrowing_id')->nullable(false)->change();
        });
    }
}; 