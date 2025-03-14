<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InStock extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'instock';
    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'integer';
     /**
     * @var array
     */
    protected $fillable = ['productid', 'user_id', 'listingdate', 'quantity', 'guid','created_at', 'updated_at'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function products()
    {
        return $this->belongsTo('App\Models\Product', 'productid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }

}
