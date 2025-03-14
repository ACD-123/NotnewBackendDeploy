<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('model')->nullable();
            $table->string('brand')->nullable();
            $table->integer('stockcapacity');
            $table->boolean('selling_now')->default(false);
            // $table->boolean('listing')->default(false);
            $table->date('listing')->nullable()->default(null);
            $table->boolean('buyitnow')->default(false);
            $table->boolean('deliverd_domestic')->default(false);
            $table->boolean('deliverd_international')->default(false);
            $table->string('company');
            $table->string('country');
            $table->double('shipping_price')->default(0);
            $table->timestamp('shipping_start')->nullable();
            $table->timestamp('shipping_end')->nullable();
            $table->float('return_shipping_price')->default(0);
            $table->integer('return_ship_duration_limt')->nullable();
            $table->string('return_ship_paid_by')->nullable();
            $table->integer('return_ship_location')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            //
        });
    }
};
