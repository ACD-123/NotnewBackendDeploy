<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\ProductsCategories
 *
 * @property integer $id
 * @property integer $product_id
 * @property integer $category_id
 * @property string $created_at
 * @property string $updated_at
 * @property Category $category
 * @property Product $product
 * @property-read \App\Models\Category $categories
 * @property-read \App\Models\Product $products
 * @method static \Illuminate\Database\Eloquent\Builder|ProductsCategories newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductsCategories newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductsCategories query()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductsCategories whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductsCategories whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductsCategories whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductsCategories whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductsCategories whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ProductsCategories extends Model
{
    /**
     * @var array
     */
    protected $fillable = ['product_id', 'category_id', 'created_at', 'updated_at'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function categories()
    {
        return $this->belongsTo(Category::class,"category_id");
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function products()
    {
        return $this->belongsTo(Product::class,"product_id");
    }
}
