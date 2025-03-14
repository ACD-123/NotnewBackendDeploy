<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserStoreVouchers extends Model
{
    protected $table='user_store_vouchers';

    protected $fillable=['user_id','store_id','coupon_code','order_id'];
}