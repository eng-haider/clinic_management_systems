<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\DayToOpen;
class DayToOpenResource extends JsonResource
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
            'work_day'=>$this->work_day,
            'every_day'=>$this->every_day,
            'status'=>$this->status,
            'reservation_type'=>$this->reservation_type,
            'reservations_number'=>$this->reservations_number,
            'time_to_open'=>TimeToOpenResource::collection($this->time_to_open),
        ];
       
    }
}
