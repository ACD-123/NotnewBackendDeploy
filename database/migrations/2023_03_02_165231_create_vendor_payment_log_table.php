<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVendorPaymentLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vendor_payment_log', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('payment_status')->nullable();
            $table->float('Amount')->nullable();
            $table->string('Curency')->nullable();
            $table->string('payment_intents')->nullable();
            $table->string('payment_method_type')->nullable();
            $table->string('payment_mode')->nullable();
            $table->string('order_id')->nullable();
            $table->double('stipe_fee')->nullable();
            $table->double('flexe_fee')->nullable();
            $table->text('meta_data')->nullable();
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
        Schema::dropIfExists('vendor_payment_log');
    }
}
