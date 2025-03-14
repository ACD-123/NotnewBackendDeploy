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
        Schema::create('bank', function (Blueprint $table) {
            $table->bigIncrements('id')->index();
            $table->string('fullname');
            $table->string('shortname');
            $table->string('phone');
            $table->string('address');
            $table->string('zip');
            $table->uuid('guid')->unique()->default(\App\Helpers\GuidHelper::getGuid());
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
        Schema::dropIfExists('bank');
    }
};
