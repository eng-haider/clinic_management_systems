<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DriverResource extends JsonResource
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
        'user'=>new UserResource($this->user),
        'car_number'=>$this->car_number,
        'owner_delivery'=>new OwnerResource($this->owner_delivery),
        'car_owner_name'=>$this->car_owner_name,
        'status'=>$this->status,
        'created_at'=>$this->created_at
      ];


    }
}
