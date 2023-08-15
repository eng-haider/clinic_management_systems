<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class LastCategoryResource extends JsonResource
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
            'name'=>$this->last_category_name,
            'category_id'=>$this->sub_category_id ,
            'images'=>$this->image,
            'category'=>$this->sub_category,
            'images'=>$this->image,
            'icon'=>$this->icon,
            'created_at'=>$this->created_at->format('d-m-Y')

        ];
    }
}
