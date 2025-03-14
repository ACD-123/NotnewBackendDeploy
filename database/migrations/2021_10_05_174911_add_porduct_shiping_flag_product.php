<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPorductShipingFlagProduct extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('has_shipping')->default(false)->nullable();
            $table->unsignedBigInteger('shipping_type')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *.env
     * @return void
     */
    public function down()
    {
        //
    }
}
