<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 3/24/2021
 * Time: 5:13 AM
 */

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class SoldScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        $builder->where('is_sold', false);
    }
}
