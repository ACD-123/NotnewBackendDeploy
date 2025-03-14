<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeHasShippingDatatype extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // DB::statement('ALTER TABLE products ALTER COLUMN has_shipping DROP DEFAULT, 
        // ALTER COLUMN has_shipping TYPE integer USING has_shipping::integer;');
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedTinyInteger('shipment_type')->nullable();
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
}
