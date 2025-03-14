<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Help extends Model
{
    use HasFactory;
    
    protected $table='help';

    protected $fillable=['user_id','message','image','subject'];

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }
}
