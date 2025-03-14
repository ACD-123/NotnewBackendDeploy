<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Auth;

class SellerData extends Model
{
    const MEDIA_UPLOAD = 'SellerData';
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'seller_datas';
    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'integer';
    /**
     * @var array
     */
    // protected $fillable = ['user_id', 'country_id', 'state_id', 'featured', 'city_id', 'fullname', 'email', 'phone', 'address',  'zip',  'active',  'password',  'password_confirmation','created_at', 'updated_at', 'description'];
    protected $fillable = ['user_id', 'cover_image', 'country_id', 'state_id', 'featured', 'city_id', 'fullname', 'email', 'phone', 'address',  'zip',  'active',  'password',  'password_confirmation','created_at', 'updated_at', 'description','main_image','video'];
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
     
     protected $appends = ['is_favourite','favourite_count','is_following','total_followers'];
    
     public function getFullnameAttribute($value)
     {
        return ucfirst($value);
     }
    public function getIsFavouriteAttribute()
    {
        if (Auth::guard('api')->check()) {
           return Favourite::isFavourite($this->guid,'2',Auth::guard('api')->id());
        }
        return false;
    }

    public function getFavouriteCountAttribute()
    {
        return Favourite::favouriteCount($this->guid,'2');
    }
    public function getIsFollowingAttribute()
    {
        if (Auth::guard('api')->check()) {
           return Follower::isFollowing($this->user_id,Auth::guard('api')->id());
        }
        return false;
    }

    public function getTotalFollowersAttribute()
    {
        return Follower::getFollowersCount($this->user_id);
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function country()
    {
        return $this->belongsTo(Countries::class, 'country_id');
    }

    public function states()
    {
        return $this->belongsTo(State::class, 'state_id');
    }

    public function cities()
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    public function cart(){
        return $this->hasMany(UserCart::class);
    }
    
    public function feedback(){
        
        return $this->hasMany(FeedBack::class, 'store_id');
        
    }
    
    public function withFeedBack(){
        
        return $this->load('feedback');
        
    }
    public function media()
    {
        return $this->hasMany(Media::class);
    }
    // public function products()
    // {
    //     return $this->hasMany(\App\Models\Product::class, 'shop_id');
    // }
  
    public function products()
    {
        return $this->hasMany(Product::class,'shop_id');
    }
    public function withProducts(){
        $this->load('products');
    }
}
