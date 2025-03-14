<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property integer $seller_id
 * @property integer $buyer_id
 * @property integer $shipping_detail_id
 * @property integer $product_id
 * @property integer $type_id
 * @property integer $status
 * @property string $created_at
 * @property string $updated_at
 * @property string $tracking_id
 * @property User $seller
 * @property User $buyer
 * @property ShippingDetail $shippingDetail
 * @property Product $product
 */
class Order extends Model
{
    const STATUS_UNCAPTURED = 'UNCAPTURED',
        STATUS_UNPAID = 'UNPAID',
        STATUS_PAID = 'PAID',
        STATUS_REFUNDED = 'REFUNDED',
        DELIVERED = 'delivered',
        COMPLETED = 'completed';

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
        'seller_id',
        'buyer_id',
        'shipping_detail_id',
        'product_id',
        'type_id',
        'payment_intent',
        'status',
        'created_at',
        'updated_at',
        'offer_id',
        'price',
        'actual_price',
        'tracking_id',
        'fedex_shipping',
        'prices',
        'shipping_rates',
        'ounces',
        'vendorstatus',
        'vendorshipmenttype',
        'deliver_status',
        'completed_timestamp'
    ];
    protected $casts = [
        'created_at'  => 'date:Y-m-d',
    ];
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function seller()
    {
        return $this->belongsTo('App\Models\User', 'seller_id');
    }

    public function buyer()
    {
        return $this->belongsTo('App\Models\User', 'buyer_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function shippingDetail()
    {
        return $this->belongsTo('App\Models\ShippingDetail','shipping_detail_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product()
    {
        return $this->belongsTo('App\Models\Product');
    }
    public function refund()
    {
    return $this->hasOne(Refund::class);
    }
    public function offer()
    {
        // return $this->hasOne(Offer::class);
        return $this->belongsTo('App\Models\Offer');
    }
    public static function defaultSelect()
    {
        return ['product_id'];
    }
    public static function statuses() {
        return [
            self::STATUS_UNCAPTURED,
            self::STATUS_UNPAID,
            self::STATUS_PAID,
            self::STATUS_REFUNDED
        ];
    }

}
