<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class BillsCollection extends ResourceCollection
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
            'data' => BillsResource::collection($this->collection),
            'meta' => ['count' => $this->collection->count(),
            'counts' => ['amount' => $this->collection->sum('amount'),
                       'podium_Deduction'=>$this->collection->sum('amount')*0.02],
        
        ],
            
            'sucess'=>true
           ];
    }
}
