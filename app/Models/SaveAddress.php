<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaveAddress extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'save_address';
    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'integer';
     /**
     * @var array
     */
    protected $fillable = ['user_id', 'product_id', 'address', 'city', 'state', 'zip', 'created_at', 'updated_at'];
    
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

}
