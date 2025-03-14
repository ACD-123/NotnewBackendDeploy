<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostProductImage extends Model
{
    protected $table = 'post_product_images';

    protected $fillable=['post_product_id','image'];

    public function product()
    {
        return $this->belongsTo(PostProduct::class,'post_product_id');
    }
}