<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use App\Models\Category;
use App\Http\Resources\CategoryResource;
use App\Transformers\SubCategoryTransformer;
use App\Transformers\FurtherCategoryTransformer;

class RootCategoryTransformer extends TransformerAbstract
{
    /**
     * List of resources to automatically include
     *
     * @var array
     */
    protected array $defaultIncludes = [
        // 'subcategories'
    ];

    /**
     * List of resources possible to include
     *
     * @var array
     */
    protected array $availableIncludes = [
        'subcategories'

    ];

    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(Category $category)
    {
        // https://github.com/spatie/laravel-fractal
        return [
            'id' => $category->id,
            'name' => $category->name,
            'category_id' => $category->category_id,
            // 'categories' => CategoryResource::collection($category->subcategories),
        ];
    }

    public function includeSubCategories(Category $category)
    {
        if (!$category->subcategories->isEmpty()) {
            $transform = RootCategoryTransformer::setdefaultIncludes(['subcategories']);
            $subcategories = $category->subcategories;
            return $this->collection($subcategories, $transform);
        }
    }
}

// "league/fractal": "^0.20.1",
// "yajra/laravel-datatables-fractal": "^9.1"
