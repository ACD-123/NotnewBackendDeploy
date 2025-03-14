<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cities';
    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'integer';
     /**
     * @var array
     */
    protected $fillable = ['name', 'state_id', 'created_at', 'updated_at'];

    public function user()
    {
        return $this->hasMany('App\Models\User');
    }
}
