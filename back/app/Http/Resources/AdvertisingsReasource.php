<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AdvertisingsReasource extends JsonResource
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
            'advertisingable_id'=>$this->advertisingable_id,
            'advertising_type_id'=>$this->advertising_type_id,
            'advertisingType'=>$this->advertisingType,
            'remaining_days_num'=>$this->remaining_days_num,
            'item'=>$this->advertisingable,
            'is_admin_approved'=>$this->is_admin_approved,
            'adv_description'=>$this->adv_description,
            'image'=>$this->image,
            'active'=>$this->active,
            'owner_id'=>$this->owner_id,
            'is_valid'=>$this->is_valid,
            'owner'=>['id'=>$this->owner->id,'owner_barnd_name'=>$this->owner->owner_barnd_name,'owner_phone'=>$this->owner->owner_phone],
            'created_at'=>$this->created_at->format('d-m-Y')

        ];
    }
}
