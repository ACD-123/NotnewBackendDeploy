<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Category
 *
 * @property integer $id
 * @property string $name
 * @property string $description
 * @property string $type
 * @property boolean $other
 * @property string $created_at
 * @property string $updated_at
 * @property ProductsCategories[] $productsCategories
 * @property ServicesCategories[] $servicesCategories
 * @property bool $active
 * @property bool $self_active
 * @method static \Illuminate\Database\Eloquent\Builder|Category newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Category newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Category query()
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereSelfActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Category extends Model
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
    protected $fillable = ['name', 'guid', 'description', 'type', 'active', 'created_at', 'updated_at', 'parent_id','underage'];

    const PRODUCT = 'Product',
        SERVICE = 'Service';
    const MEDIA_UPLOAD = "Category";

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function productsCategories()
    {
        return $this->hasMany('App\Models\ProductsCategories');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function servicesCategories()
    {
        return $this->hasMany('App\Models\ServicesCategories');
    }

    public function categoryAttributes()
    {
        return $this->hasMany('App\Models\CategoryAttributes');
    }

    public function attributes()
    {
        return $this->belongsToMany(Attribute::class, 'category_attributes');
    }
    public static function defaultSelect()
    {
        return ['id','name', 'guid', 'description', 'type', 'active', 'has_shipping', 'created_at', 'updated_at', 'parent_id'];
    } 
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }
     public function brands()
    {
        return $this->belongsToMany(Brands::class);
    }
    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function childrenRecursive()
    {
        return $this->children()->with('childrenRecursive');
    }

    public function media()
    {
        return $this->hasMany('App\Models\Media');
    }
}
