<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Helpers\GuidHelper;

class CreateUnitType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('unit_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('active')->default(false);
            $table->uuid('guid')->unique()->default(\App\Helpers\GuidHelper::getGuid());
            $table->timestamps();
        });

        /*Schema::table('attributes_values', function (Blueprint $table) {
            $table->foreign('category_attribute_id')->references('id')->on('category_attributes')->onDelete('cascade')->onUpdate('cascade');
        });*/

        Schema::table('category_attributes', function (Blueprint $table) {
            $table->foreign('attribute_id')->references('id')->on('attributes')->onDelete('cascade')->onUpdate('cascade');
            // $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('unit_type_id')->references('id')->on('unit_types')->onDelete('cascade')->onUpdate('cascade');
        });

        /*Schema::table('product_attributes', function (Blueprint $table) {
            $table->foreign('attribute_id')->references('id')->on('attributes')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('unit_type_id')->references('id')->on('unit_types')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade')->onUpdate('cascade');
        });*/
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('unit_type');
    }
}
