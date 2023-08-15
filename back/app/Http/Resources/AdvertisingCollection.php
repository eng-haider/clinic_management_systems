<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class AdvertisingCollection extends ResourceCollection
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
            'data' => AdvertisingsReasource::collection($this->collection),
            'meta' => ['ActiveOwnerAdverNum' => $this->collection->where('is_admin_approved','=',1)->where('advertisingable_type','=','App\Owner')->count(),
            'ActiveItemsAdverNum' => $this->collection->where('is_admin_approved','=',1)->where('advertisingable_type','=','App\Item')->count()
        ],
            'sucess'=>true
           ];
    }
}
