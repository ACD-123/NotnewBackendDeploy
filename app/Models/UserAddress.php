<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAddress extends Model
{
    use HasFactory;
    
    protected $table='user_address';

    protected $fillable=['user_id','city','country','state','zip','address','latitude','longitude','label','address','street'];
    
     public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }
}
