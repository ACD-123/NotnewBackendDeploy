<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class PostProduct extends Model
{
    protected $table = 'post_products';

    protected $fillable=['guid','title','description','post_category_id','post_type','main_image','address','city','country','zip','latitude','longitude','condition','firm_price','virtual_tour','price','delivery_method','user_id','promoted','promoted_date','status','underage','guid'];
    protected $appends = ['date','is_favourite','favourite_count'];

    public function getDateAttribute()
    {
        return Carbon::parse($this->created_at)->diffForHumans();
    }
    public function getIsFavouriteAttribute()
    {
        if (Auth::guard('api')->check()) {
           return Favourite::isFavourite($this->guid,3,Auth::guard('api')->id());
        }
        return false;
    }
    public function getFavouriteCountAttribute()
    {
        return Favourite::favouriteCount($this->guid,3);
    }

    public function category()
    {
        return $this->belongsTo(PostCategory::class,'post_category_id');
    }
    public function extra()
    {
        return $this->hasMany(PostProductFeature::class,'post_product_id','id');
    }
    public function images()
    {
        return $this->hasMany(PostProductImage::class,'post_product_id','id');
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}