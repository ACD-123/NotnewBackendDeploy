<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostAttribute extends Model
{
    protected $table = 'post_attributes';

    protected $fillable=['guid','name','type','options'];
}