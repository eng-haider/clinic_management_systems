<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ItemsCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
       return [
        'data' => ItemsResource::collection($this->collection),
        'meta' => ['items_count' => $this->collection->count()],
       ];
    }

    public function with($request)
    {
            return ['success'=>'success'];
    }
}
