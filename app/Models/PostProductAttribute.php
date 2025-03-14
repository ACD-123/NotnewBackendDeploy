<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostProductAttribute extends Model
{
    protected $table = 'post_product_attributes';

    protected $fillable = ['attribute_id', 'product_id','value'];

    public function product()
    {
        return $this->belongsTo(PostProduct::class,'product_id');
    }
    public function attribute()
    {
        return $this->belongsTo(PostAttribute::class,'attribute_id');
    }
}