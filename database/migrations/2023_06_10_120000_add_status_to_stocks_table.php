<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatusToStocksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('stocks', function (Blueprint $table) {
            // Kitabın fiziksel durumu için condition alanını varsayılan 'good' olarak tanımlayalım
            if (!Schema::hasColumn('stocks', 'condition')) {
                $table->string('condition')->default('good')->after('quantity')->comment('Kitabın fiziksel durumu: new, good, fair, poor');
            }
            
            // Kitabın ödünç durumu için status alanı ekleyelim
            if (!Schema::hasColumn('stocks', 'status')) {
                $table->string('status')->default('available')->after('condition')->comment('Kitabın ödünç durumu: available, borrowed, reserved, lost, damaged');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('stocks', function (Blueprint $table) {
            if (Schema::hasColumn('stocks', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
} 