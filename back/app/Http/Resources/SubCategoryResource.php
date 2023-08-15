<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SubCategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'=>$this->id,
            'name'=>$this->sub_category_name,
            'category_id'=>$this->category_id,
            'images'=>$this->image,
            'category'=>$this->category,
            'LastCategory'=>LastCategoryResource::collection($this->LastCategory),
            'images'=>$this->image,
            'icon'=>$this->icon,
            'created_at'=>$this->created_at->format('d-m-Y')

        ];
    }
}
