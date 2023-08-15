<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SubTimeResource extends JsonResource
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
            'time_to_open_id'=>$this->time_to_open_id  ,
            'from_sub_time'=>$this->from_sub_time,
            'to_sub_time'=>$this->to_sub_time,
            'time_to_open'=>$this->time_to_open->reservation_type_id,
            'active'=>$this->active,
        
        ];
    }
}
