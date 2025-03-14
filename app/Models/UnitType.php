<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property string $name
 * @properties AttributesValue[] $attributesValues
 * @properties ProductAttribute[] $productAttributes
 *  @mixin Builder
 */
class UnitType extends Model
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
    protected $fillable = ['name'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function attributesValues()
    {
        return $this->hasMany('App\AttributesValue');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function productAttributes()
    {
        return $this->hasMany('App\ProductAttribute');
    }

    public static function getAll(): Builder
    {
        return self::where("active", true);
    }
}
