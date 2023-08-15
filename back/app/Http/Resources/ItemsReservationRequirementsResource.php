<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ItemsReservationRequirementsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return
        [
            'id'=>$this->id,
            'item_id'=>$this->item_id,
            'requirement_name'=>$this->requirement_name,
            'created_at'=>$this->created_at->format('d-m-Y')
        ];
    }
}
