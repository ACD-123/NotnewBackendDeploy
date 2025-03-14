<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportSeller extends Model
{
    use HasFactory;
    
    protected $table='report_seller';

    protected $fillable=['user_id','message','reason','seller_guid'];
    
    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }

    public function seller()
    {
        return $this->belongsTo(SellerData::class, 'seller_guid', 'guid');
    }
}
