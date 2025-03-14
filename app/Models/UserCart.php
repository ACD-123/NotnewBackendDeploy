<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserCart extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tbl_cart';
    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'integer';
     /**
     * @var array
     */
    protected $fillable = ['product_id', 'user_id','guest_user_id', 'name','price', 'quantity', 'attributes','shop_id','coupon_code','is_auctioned', 'created_at', 'updated_at'];

     /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    // public function products()
    // {
    //     return $this->belongsTo('App\Models\Product','product_id');
    // }
    
    public function products()
    {
        return $this->belongsTo('App\Models\Product', 'product_id');
    }
    public function shop(){
        return $this->belongsTo('App\Models\SellerData', 'shop_id');
    }
    public function savelater(){

        return $this->belongsTo('App\Models\SaveCartLater');

    }
    public function checkout(){

        return $this->belongsTo('App\Models\CheckOut');

    }
}
