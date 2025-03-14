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
class Prices extends Model
{
    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'integer';
    /**
     * @var array
     */
    protected $fillable = ['name', 'value', 'active'];

    public static function getAll(): Builder
    {
        return self::where("active", true);
    } 
}
