<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class OwnerSubscriptionsCollection extends ResourceCollection
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
            'data' => OwnerSubscriptionsResource::collection($this->collection),
            'meta' => ['OwnerSubscriptions' => $this->collection->count()],
             'sucess'=>true
           ];
    }
}
