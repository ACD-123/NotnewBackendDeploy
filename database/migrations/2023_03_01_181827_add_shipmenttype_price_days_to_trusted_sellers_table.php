<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddShipmenttypePriceDaysToTrustedSellersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('trusted_sellers', function (Blueprint $table) {
            $table->string('shipmenttype')->nullable();
            $table->string('price')->nullable();
            $table->string('days')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('trusted_sellers', function (Blueprint $table) {
            //
        });
    }
}
