<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 1/23/2021
 * Time: 3:40 AM
 */

namespace App\Traits;

use App\Helpers\ArrayHelper;
use App\Models\Category;
use Illuminate\Database\Eloquent\Builder;

trait AppliesQueryParams
{
    public function applyFilters(&$request): callable
    {

        return function (Builder $query) use (&$request) {
            $query->when($request->get('id'), function (Builder $query, $id) {
                return $query->where('id', (int)$id);
            })->when($request->get('active'), function (Builder $query, $active) {
                return $query->where('active', $active);
            })->when($request->get('category_type'), function (Builder $query, $type) {
                return $query->where('type', $type == 1 ? Category::PRODUCT : Category::SERVICE);
            })->when($request->get('is_sold'), function (Builder $query, $active) {
                return $query->where('is_sold', $active);
            })->when($request->get('categories'), function (Builder $query, $categories) {
                if (ArrayHelper::isArray($categories)) {
                    $selected_categories = Category::whereIn('parent_id', $categories)->get('id');
                    return $query->whereIn('category_id', $selected_categories);
                }
                $selected_categories = Category::where('parent_id', $categories)->get('id');
                return $query->whereIn('category_id', $selected_categories);
            });
        };
    }
}
