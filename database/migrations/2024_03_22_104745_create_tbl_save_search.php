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
        Schema::create('tbl_save_search', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('keywords');
            $table->boolean('email_alert')->default(false);
            $table->unsignedBigInteger('user_id')->index();
            $table->uuid('guid')->unique()->default(\App\Helpers\GuidHelper::getGuid());
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
        Schema::dropIfExists('tbl_save_search');
    }
};
