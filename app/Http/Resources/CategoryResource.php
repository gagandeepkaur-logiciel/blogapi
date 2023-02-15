<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    // protected $defaultIncludes = [
    //     'subcategories',
    // ];
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        // return parent::toArray($request);
        return [
            'id' => $this->id,
            'name' => $this->name,
            'category_id' => $this->category_id,
            'subcategory' => CategoryResource::collection($this->subcategories),
            // 'subcat' => $this->subcategories,
        ];
    }
}
