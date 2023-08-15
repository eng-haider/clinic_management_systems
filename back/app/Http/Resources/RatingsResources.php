<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RatingsResources extends JsonResource
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
            'rate_value'=>$this->rate_value,
            'item'=>$this->rateable,
            'comment'=>$this->comment,
            'user'=>$this->user,
            'created_at'=>$this->created_at->format('d-m-Y')
          

            ];
    }
}
