<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserOrder extends Model
{
    const 
        STATUS_PENDING='pending',
        STATUS_ORDERED='ordered',
        STATUS_UNPAID = 'UNPAID',
        STATUS_PAID = 'PAID',
        STATUS_REFUNDED = 'REFUNDED',
        DELIVERED = 'delivered',
        COMPLETED = 'completed',
        PAYMENTTYPE = 'Card',
        REFUND = 'refund';
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tbl_user_order';
    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'integer';

    /**
     * @var array
     */
    protected $fillable = [
        'orderid',
        'buyer_id',
        'seller_id',
        'payment_type',
        'billing_address',
        'fullname',
        'phone',
        'address',
        'discountcode',
        'orderItems',
        'subtotal_cost',
        'actual_cost',
        'shipping_cost',
        'prices',
        'order_total',
        'status',
        'deliver_status',
        'deliver_at',
        'payment_intents',
        'Curency',
        'admin_notes',
        'shipping_detail_id',
        'delivered_at',
        'customer_email_sent',
        'client_secret',
        'shipment_paymentIntents',
        'shipment_clientSecret',
        'parcel_size',
        'parcel_width',
        'parcel_height',
        'parcel_length',
        'delivery_days',
        'read_by_admin',
        'product_id',
        'order_type',
        'estimateDelivery',
        'latitute',
        'longitude',
        'zip',
        'country',
        'state',
        'city',
        'completed_timestamp'
    ];
    protected $casts = [
        'created_at'  => 'date:Y-m-d',
    ];
    
    public function buyer()
    {
        return $this->belongsTo('App\Models\User', 'buyer_id');
    }

    public function seller()
    {
        return $this->belongsTo('App\Models\User', 'seller_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function shippingDetail()
    {
        return $this->belongsTo('App\Models\ShippingDetail','shipping_detail_id');
    }
    
    public function orderDetails()
    {
        // return $this->belongsToMany(UserOrderDetails::class);
        // return $this->hasMany(UserOrderDetails::class);
        return $this->hasMany(UserOrderDetails::class, 'order_id');
    }
    
    public function refund()
    {
        return $this->hasMany(Refund::class, 'id','order_id');
    }
    // 
    public static function statuses() {
        return [
            self::STATUS_UNPAID,
            self::STATUS_PAID,
            self::STATUS_REFUNDED,
            self::COMPLETED,
            self::STATUS_PENDING,
            self::REFUND,
        ];
    }
}
