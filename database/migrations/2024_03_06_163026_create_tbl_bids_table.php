<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_bids', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->float('max_bids')->default(0);
            $table->float('shipping_charges')->default(0);
            $table->string('time_bids');
            $table->float('estimated_total')->default(0);
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('product_id')->index();
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            // id -> auto increament
            // user_id -> user id
            // product_id -> product id
            // time of bids
            // max_bids
            // shipping_charges
            // estimated_total
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_bids');
    }
};
