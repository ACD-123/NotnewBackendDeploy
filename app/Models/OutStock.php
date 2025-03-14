<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OutStock extends Model
{
     /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'outstock';
    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'integer';
     /**
     * @var array
     */
    protected $fillable = ['productid', 'user_id', 'saledate', 'quantity', 'guid','created_at', 'updated_at'];
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function products()
    {
        return $this->belongsTo('App\Models\Product');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

}
