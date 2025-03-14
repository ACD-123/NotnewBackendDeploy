<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bidding extends Model
{
    use HasFactory;

    protected $fillable=['user_id','product_id','bid_amount','seller_guid','status','date'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function getTotalBids($product_id)
    {
        return Bidding::where('product_id',$product_id)->count();  
    }
    public static function getMaxBid($product_id)
    {
        return Bidding::where('product_id', $product_id)->max('bid_amount')??0;
    }
    public static function getMaxBidUser($product_id)
    {
        $maxBid = Bidding::where('product_id', $product_id)
            ->orderByDesc('bid_amount')
            ->first();
        return $maxBid ? $maxBid->user : null;
    }
    
}