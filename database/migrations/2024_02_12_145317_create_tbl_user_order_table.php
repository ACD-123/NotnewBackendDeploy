<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\UserOrder;
return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_user_order', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('orderid');
            $table->unsignedBigInteger('buyer_id')->index();
            $table->unsignedBigInteger('seller_id')->index();
            $table->string('payment_type');
            $table->text('billing_address');
            $table->string('fullname');
            $table->string('phone');
            $table->text('address');
            $table->string('discountcode')->nullable();
            $table->jsonb('orderItems');
            $table->float('subtotal_cost');
            $table->float('actual_cost');
            $table->float('shipping_cost');
            $table->jsonb('prices');
            $table->float('order_total');
            $table->enum('status', [UserOrder::statuses()])->default(UserOrder::STATUS_PENDING);
            $table->string('deliver_status')->default(false);
            $table->timestamp('deliver_at');
            $table->string('payment_intents')->comment('Stripe returns this after successful payment hold')->nullable();
            $table->string('Curency')->nullable();
            $table->string('admin_notes')->nullable();
            $table->unsignedBigInteger('shipping_detail_id')->nullable();
            $table->timestamp('delivered_at')->useCurrent();
            $table->boolean('customer_email_sent')->default(false);
            $table->string('client_secret')->nullable();
            $table->string('shipment_paymentIntents')->comment('Stripe returns this after successful payment hold after Shipment')->nullable();
            $table->string('shipment_clientSecret')->nullable();
            $table->float('parcel_size')->default(0);
            $table->float('parcel_width')->default(0);
            $table->float('parcel_height')->default(0);
            $table->string('parcel_length')->default(0);
            $table->integer('delivery_days')->default(0);
            $table->boolean('read_by_admin')->default(false);
            $table->foreign('buyer_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('seller_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('shipping_detail_id')->references('id')->on('shipping_details')->onDelete('cascade')->onUpdate('cascade');
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
        Schema::dropIfExists('tbl_user_order');
    }
};
