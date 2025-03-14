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
        Schema::create('outstock', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('productid');
            $table->unsignedBigInteger('user_id');
            $table->date('saledate')->default(null);
            $table->double('quantity');
            $table->uuid('guid')->unique();
            $table->foreign('productid')->references('id')->on('products')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
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
        Schema::dropIfExists('outstock');
    }
};
