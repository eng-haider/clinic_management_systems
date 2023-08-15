<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Http\Resources\RatingsResources;
class RatingsOwnerCollection extends ResourceCollection
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
            'data' => RatingsResources::collection($this->collection),
            'meta' => ['items_count' => $this->collection->count()],
           ];
        
    }
}
