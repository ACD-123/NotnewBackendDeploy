<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CheckOut extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tbl_checkout';
    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'integer';
     /**
     * @var array
     */
    protected $fillable = ['cart_id', 'user_id', 'dicount_code','items_number', 'sub_total', 'shipping_total',
        'admin_prices', 'order_total', 'created_at', 'updated_at', 'store_id', 'product_id', 'quantity','attri'];

     /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function cart(){

        return $this->belongsTo('App\Models\UserCart');
    }
    
    
    public function product(){

        return $this->belongsTo('App\Models\Product', 'product_id');
    }

    public function store(){

        return $this->belongsTo('App\Models\SellerData', 'store_id');
    }
}
