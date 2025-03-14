<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateColumnsInPaymentlogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payments_log', function (Blueprint $table) {
            $table->double('order_id')->nullable();
            $table->double('stipe_fee')->nullable();
            $table->double('flexe_fee')->nullable();
            $table->text('meta_data')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payments_log', function (Blueprint $table) {
            //
        });
    }
}
