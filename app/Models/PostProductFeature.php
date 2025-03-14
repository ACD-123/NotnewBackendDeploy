<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostProductFeature extends Model
{
    protected $table = 'post_product_features';

    protected $fillable=['post_product_id','title'];

    public function product()
    {
        return $this->belongsTo(PostProduct::class,'post_product_id');
    }
}