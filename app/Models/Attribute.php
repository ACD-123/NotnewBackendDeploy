<?php

namespace App\Models;

use App\Core\Base;
use App\Helpers\StringHelper;
use Illuminate\Database\Eloquent\Builder;

/**
 * App\Models\Attribute
 *
 * @property integer $id
 * @property string $name
 * @properties AttributesValue[] $attributesValues
 * @properties ProductAttribute[] $productAttributes
 * @method static \Illuminate\Database\Eloquent\Builder|Attribute newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Attribute newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Attribute query()
 * @method static \Illuminate\Database\Eloquent\Builder|Attribute whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attribute whereName($value)
 * @mixin \Eloquent
 */
class Attribute extends Base
{
    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */

    const TEXT = 'TEXT',
        CHECKBOX = 'CHECKBOX',
        CHECKBOX_GROUP = 'CHECKBOX_GROUP',
        RADIO_GROUP = 'RADIO_GROUP',
        FILE = 'FILE',
        COLOR_PICKER = 'COLOR_PICKER',
        SELECT = 'SELECT',
        RADIO ='RADIO';

//    protected $hasGuid = false;
    protected $autoBlame = false;
    /**
     * @var array
     */
    protected $fillable = ['name', 'type', 'options', 'active'];

    protected $casts = ['options' => 'array'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function attributesValues()
    {
        return $this->hasMany('App\AttributesValue');
    }

    public function setNameAttribute($name)
    {
        $this->attributes['name'] = StringHelper::trimLower($name);
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
    public static function defaultSelect()
    {
        return ['id', 'name', 'type', 'options', 'active'];
    } 
    public static function types(): array
    {
        return [
            static::TEXT => 'Text',
            static::CHECKBOX => 'Checkbox',
            static::CHECKBOX_GROUP => 'Checkbox Group',
            static::RADIO_GROUP => 'Radio Group',
            static::SELECT => 'Select',
            static::FILE => 'File',
            static::COLOR_PICKER => 'Color Picker',
            static::RADIO => 'Radio'
        ];
    }

    public static function typeKeys(): array
    {
        return array_keys(static::types());
    }

    public static function typesWithOptions(): array
    {
        return [
            static::CHECKBOX_GROUP,
            static::RADIO_GROUP,
            static::SELECT
        ];
    }
}
