<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Http\Resources\RatingsResources;
class RatingsCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {

        return[

            'rate' => ['Ratings' => $this->collection->avg('rate_value')],
            'data' => RatingsResources::collection($this->collection),
            
           ];
        
    }
}
