<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProductShipingDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
//        Schema::create('product_shipping_details', function (Blueprint $table) {
//            $table->bigIncrements('id');
//            $table->string('type');
//            $table->string('size');
//            $table->unsignedBigInteger('user_id');
//            $table->unsignedBigInteger('product_id');
//            $table->uuid('guid')->unique();
//            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade')->onUpdate('cascade');
//            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
//            $table->timestamps();
//        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
//        Schema::dropIfExists('product_shipping_details');
    }
}
