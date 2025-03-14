<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model 
{
    protected $fillable = ['title','active','url','featured','featured_by','featured_until','type','guid','underage'];

    public function media()
    {
        return $this->hasMany('App\Models\Media');
    }
}