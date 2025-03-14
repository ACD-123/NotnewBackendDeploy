<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserBank extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'userbank';
    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'integer';
    /**
     * @var array
     */
    protected $fillable = ['user_id', 'bank_id', 'accountName', 'accountNumber', 'bic_swift', 'guid','created_at', 'updated_at'];
    /**
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function bank()
    {
        return $this->belongsTo(Bank::class, 'bank_id');
    }
}
