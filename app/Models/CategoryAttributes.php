<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property integer $attribute_id
 * @property integer $category_id
 * @property integer $unit_type_id
 * @property string $value
 * @property string $created_at
 * @property string $updated_at
 * @property Attribute $attribute
 * @property Category $category
 * @property UnitType $unitType
 * @properties AttributesValue[] $attributesValues
 */
class CategoryAttributes extends Model
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
    protected $fillable = ['attribute_id', 'category_id', 'unit_type_id', 'created_at', 'updated_at'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function attribute()
    {
        return $this->belongsTo('App\Models\Attribute');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category()
    {
        return $this->belongsTo('App\Models\Category');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function unitType()
    {
        return $this->belongsTo('App\Models\UnitType');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function attributesValues()
    {
        return $this->hasMany('App\Models\AttributesValue');
    }
}
