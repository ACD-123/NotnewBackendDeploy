<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostCategory extends Model
{
    protected $table = 'post_categories';

    protected $fillable=['guid','title','image'];

    public function products()
    {
        return $this->hasMany(PostProduct::class,'post_category_id','id');
    }
}