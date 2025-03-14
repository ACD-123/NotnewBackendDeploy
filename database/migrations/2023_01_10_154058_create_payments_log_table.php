<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentsLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payments_log', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('payment_status')->nullable();
            $table->float('Amount')->nullable();
            $table->string('Curency')->nullable();
            $table->string('payment_intents')->nullable();
            $table->string('Customer')->nullable();
            $table->string('payment_method_type')->nullable();
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
        Schema::dropIfExists('payments_log');
    }
}
