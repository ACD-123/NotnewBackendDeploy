<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeedBack extends Model
{
     /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tbl_feedback';
    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'integer';
    /**
     * @var array
     */
    protected $fillable = ['product_id','user_id', 'store_id','comments', 'seller_comments', 'admin_comments', 'guid', 'created_at', 'updated_at'];

    public function store()
    {
        return $this->belongsTo('App\Models\SellerData', 'store_id');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }
    
    public function product()
    {
        return $this->belongsTo('App\Models\Product', 'product_id');
    }
}
