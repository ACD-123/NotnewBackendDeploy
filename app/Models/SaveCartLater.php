<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaveCartLater extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tbl_save_cart_later';
    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'integer';
    /**
     * @var array
     */
    protected $fillable = ['user_id', 'cart_id','created_at', 'updated_at'];
    /**
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function cart(){
        return $this->belongsTo(UserCart::class, 'cart_id');
    }
}
