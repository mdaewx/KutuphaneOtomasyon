<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('acquisition_sources', function (Blueprint $table) {
            $table->date('acquisition_date')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('acquisition_sources', function (Blueprint $table) {
            $table->date('acquisition_date')->nullable(false)->change();
        });
    }
}; 