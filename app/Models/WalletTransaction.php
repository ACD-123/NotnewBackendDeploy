<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class WalletTransaction extends Model
{
    use HasFactory;

    protected $fillable=['user_id','amount','order_id','type','message'];

    protected $appends = ['date'];

    public function getDateAttribute()
    {
        return Carbon::parse($this->created_at)->diffForHumans();
    }

}
