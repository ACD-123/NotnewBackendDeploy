<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class flexefee extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'flexefee';
    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'integer';
     /**
     * @var array
     */
    protected $fillable = ['fee', 'created_at', 'updated_at'];
}
