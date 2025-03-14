<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SellerTransaction extends Model
{
    use HasFactory;
    
    protected $table="seller_transactions";

    protected $fillable=['seller_guid','order_id','amount','type','date','message','product_id','status','note','image'];
    
    public function seller()
    {
        return $this->belongsTo(SellerData::class, 'seller_guid', 'guid');
    }
}