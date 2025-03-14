<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductAttributes extends Model
{
     /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tbl_product_attributes';

    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'integer';

    /**
     * @var array
     */
    protected $fillable = ['name', 'value', 'product_id'];

    protected $casts = [
        'created_at' => 'date:M d, Y g:i A',
    ];
}
