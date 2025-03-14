<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductAttributes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
//        Schema::create('product_attributes', function (Blueprint $table) {
//            $table->id();
//            $table->unsignedBigInteger('category_attribute_id');
//            $table->longText('value');
//            $table->unsignedBigInteger('attribute_id')
//                ->nullable()->comment("will be used un future");
//            $table->unsignedBigInteger('unit_type_id');
//            $table->unsignedBigInteger('category_id');
//            $table->unsignedBigInteger('product_id');
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
        //   Schema::dropIfExists('product_attributes');
    }
}
