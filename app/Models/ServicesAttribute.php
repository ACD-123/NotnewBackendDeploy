<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServicesAttribute extends Model
{
    public $timestamps = false;

    protected $fillable = ['service_id', 'attribute_id', 'value'];
    protected $casts = ['value' => 'json'];
}
