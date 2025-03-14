<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecentView extends Model
{
    protected $table="recent_view";
    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'integer';

    protected $fillable = [
        'product_id',
        'created_at',
        'updated_at'
    ];   

    /**
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function products()
    {
        return $this->belongsTo(Product::class, 'product_id');
        // return $this->belongsTo('App\Models\Product');
    }

}
