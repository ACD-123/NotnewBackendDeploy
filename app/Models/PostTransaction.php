<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostTransaction extends Model
{
    protected $table = 'post_transactions';

    protected $fillable=['user_id','product_id','price','','end_date'];

    public function product()
    {
        return $this->belongsTo(PostProduct::class,'product_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }

}