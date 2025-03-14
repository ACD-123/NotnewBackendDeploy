<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsInTrustedSellerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('trusted_sellers', function (Blueprint $table) {
            $table->string('address');
            $table->string('number');
            $table->boolean('store');
            $table->string('facebook');
            $table->string('instagram');
            $table->string('ein');
            $table->string('ssn');
            $table->string('businessType');
            $table->string('website');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('trusted_sellers', function (Blueprint $table) {
            //
        });
    }
}
