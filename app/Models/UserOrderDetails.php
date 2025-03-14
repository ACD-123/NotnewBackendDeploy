<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserOrderDetails extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tbl_user_order_details';
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
        'order_id',
        'product_id',
        'price',
        'attributes',
        'guid',
        'quantity',
        'store_id',
        'refunded',
        'status',
        'created_at',
        'updated_at',
        'completed_timestamp'
    ];
    protected $casts = [
        'created_at'  => 'date:Y-m-d',
    ];
    
    public function order()
    {
        return $this->belongsTo('App\Models\UserOrder', 'order_id');
    }

    public function product()
    {
        return $this->belongsTo('App\Models\Product', 'product_id');
    }

    public function store()
    {
        return $this->belongsTo('App\Models\SellerData', 'store_id');
    }
}
