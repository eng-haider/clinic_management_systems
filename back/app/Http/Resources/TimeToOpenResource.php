<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TimeToOpenResource extends JsonResource
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
            'day_to_open_id '=>$this->day_to_open_id ,
            'from_time'=>$this->from_time,
            'to_time'=>$this->to_time,
            'reservation_type_id'=>$this->reservation_type_id,
            'max_request'=>$this->max_request,
            'every_time'=>$this->every_time,
            'day_to_open'=>$this->day_to_open->reservation_type_id,
            'status'=>$this->status,
            'reservation_type'=>$this->reservation_type,
            'reservation_duration'=>$this->reservation_duration,
            'reservations_number'=>$this->reservations_number,
            'SubTime'=>SubTimeResource::collection($this->SubTime),
            'action_id '=>$this->action_id
        ];
    }
}
