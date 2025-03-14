<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOffersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('offers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('requester_id');
            $table->unsignedBigInteger('user_id');
            $table->decimal("price");
            $table->unsignedBigInteger("status_id")->nullable();
            $table->string("status_name")->nullable();
            $table->uuid('guid')->unique();
            $table->timestamps();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->foreign('created_by')->references('id')->on('users')->cascadeOnUpdate();
            $table->foreign('updated_by')->references('id')->on('users')->cascadeOnUpdate();
            $table->foreign('requester_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade')->onUpdate('cascade');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('offer_id')->nullable();
            $table->decimal("price");
            $table->decimal("actual_price")->default(0);
            $table->foreign('offer_id')->references('id')->on('offers')->cascadeOnDelete()->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn("offer_id");
            $table->dropColumn("price");
            $table->dropColumn("actual_price");
        });

        Schema::dropIfExists('offers');
    }
}
