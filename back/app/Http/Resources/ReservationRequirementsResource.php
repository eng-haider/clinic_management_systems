<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ReservationRequirementsResource extends JsonResource
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
            'user'=>new UserResource($this->user),
            'id'=>$this->id,
            'image'=>$this->image,
            
            'ItemsReservationRequirements'=>new ItemsReservationRequirementsResource($this->ItemsReservationRequirements),
            'created_at'=>$this->created_at->format('d-m-Y')
        ];
    }
}
