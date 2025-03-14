<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Brands extends Model
{
     /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'brands';
    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'integer';
    /**
     * @var array
     */
    protected $fillable = ['name', 'guid', 'created_at', 'updated_at'];

    public function category()
    {
        return $this->belongsTo('App\Models\Category', 'category_id');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }
}
