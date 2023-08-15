<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PartnershipResource extends JsonResource
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
          'owner_delivery_id'=>$this->owner_delivery_id,
          'status'=>$this->status,
          'owner_delivery'=>new OwnerResource($this->owner_delivery),
          'owner_item'=>new OwnerResource($this->owner_items)
          ];
    }
}
