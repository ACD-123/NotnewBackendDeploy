<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Wishlist extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'product_id','product_guid'];

    public function getDateAttribute($value)
    {
        return Carbon::parse($value)->diffForHumans(); // Formats the date as "x time ago"
    }

    public static function isWishlist($id,$user_id){
        $fav = Wishlist::where([
            ['user_id',$user_id],
            ['product_guid',$id],
        ])->first();
        return $fav ? true : false;
    }
    public static function wishlistCount($id){
        $count = Wishlist::where([
            ['product_guid',$id]
        ])->count();
        return $count;
    }

}
