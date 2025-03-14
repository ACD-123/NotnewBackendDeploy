<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProductShippingColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
//
//        Schema::table('product_shipping_details', function($table) {
//            $table->dropColumn('type');
//            $table->dropColumn('size');
//            $table->dropColumn('guid');
//            $table->string('street_address');
//            $table->string('city');
//            $table->string('zip');
//            $table->string('state');
//        //    $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade')->onUpdate('cascade');
//       //     $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
//        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
     //   Schema::dropIfExists('product_shipping_details');
    }
}
