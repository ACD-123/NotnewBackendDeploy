<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\ServicesCategories
 *
 * @property integer $id
 * @property integer $service_id
 * @property integer $category_id
 * @property string $created_at
 * @property string $updated_at
 * @property Category $category
 * @property Service $service
 * @method static \Illuminate\Database\Eloquent\Builder|ServicesCategories newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ServicesCategories newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ServicesCategories query()
 * @mixin \Eloquent
 */
class ServicesCategories extends Model
{
   protected $table = 'services_categories';

    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'integer';

    /**
     * @var array
     */
    protected $fillable = ['service_id', 'category_id', 'created_at', 'updated_at'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }
}
