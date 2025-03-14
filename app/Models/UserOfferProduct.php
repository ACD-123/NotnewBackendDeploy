<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Prices
 * 
 * @property integer $id
 * @property string $name
 * @property string $value
 * @property string $created_at
 * @property bool $active
 * @mixin \Eloquent
 */
class UserOfferProduct extends Model
{
    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    protected $table = 'userofferproduct';
    protected $keyType = 'integer';
    /**
     * @var array
     */
    protected $fillable = ['userId', 'productId', 'status'];

}
