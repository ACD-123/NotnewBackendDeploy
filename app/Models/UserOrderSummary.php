<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserOrderSummary extends Model
{
   /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    protected $table = 'user_order_summary';
    protected $keyType = 'id';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * @var array
     */
    protected $fillable = ['order_id', 'product_id', 'seller_id', 'created_at', 'updated_at'];

    public function buyer()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function order()
    {
        return $this->belongsTo(UserOrder::class, 'order_id');
    }

}
