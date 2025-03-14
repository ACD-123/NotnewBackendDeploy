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
        Schema::create('notification_setting', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->boolean('order_updates')->default(false);
            $table->boolean('item_ending')->default(false);
            $table->boolean('item_updates')->default(false);
            $table->boolean('auction_updates')->default(false);
            $table->boolean('offer_updates')->default(false);
            $table->boolean('presonalized_recomendations')->default(false);
            $table->boolean('rewards_offers')->default(false);
            $table->boolean('general_promotions')->default(false);
            $table->boolean('selling_presonalized_recomendations')->default(false);
            $table->boolean('selling_rewards_offers')->default(false);
            $table->boolean('selling_general_promotions')->default(false);
            $table->unsignedBigInteger('user_id')->index();
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
        Schema::dropIfExists('notification_setting');
    }
};
