<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostCategoryAttribute extends Model
{
    protected $table = 'post_category_attributes';

    protected $fillable = ['attribute_id', 'category_id'];

    public function category()
    {
        return $this->belongsTo(PostCategory::class,'category_id');
    }
    public function attribute()
    {
        return $this->belongsTo(PostAttribute::class,'attribute_id');
    }
}