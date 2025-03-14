<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecentUserView extends Model
{
    protected $table="recent_user_view";
    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'integer';

    protected $fillable = [
        'recent_view_id',
        'user_id',
        'product_id',
        'created_at',
        'updated_at'
    ];   

    /**
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
        // return $this->belongsTo('App\Models\Product');
    }

    public function recent()
    {
        return $this->belongsTo(RecentView::class, 'recent_view_id');
        // return $this->belongsTo('App\Models\Product');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
        // return $this->belongsTo('App\Models\Product');
    }
}
