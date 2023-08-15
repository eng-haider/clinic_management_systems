<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AddressResource extends JsonResource
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
            'province_id'=>$this->province_id,
            'long'=>$this->long,
            'lat'=>$this->lat,
            'province'=>$this->province,
            'address_descraption'=>$this->address_descraption

        ];
    }
}
