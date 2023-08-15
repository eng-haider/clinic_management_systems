<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ReservationResource extends JsonResource
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
            'reservation_start_date'=>$this->reservation_start_date,
            'reservation_end_date'=>$this->reservation_end_date,
            'reservation_from_time'=>$this->reservation_from_time,
            'reservation_to_time'=>$this->reservation_to_time,
            'status'=>$this->status,
            'owner_notes'=>$this->owner_notes,
            'ReservationRequirements'=>ReservationRequirementsResource::collection($this->ReservationRequirements),
            'user_id'=>$this->user_id,
            'images'=>$this->image,
            'user'=> ['id' =>$this->user->id ,'full_name'=>$this->user->full_name,'user_phone'=>$this->user->user_phone],    
             'item'=> ['id' =>$this->item->id,'Address'=>AddressResource::collection($this->item->address),'item_name'=>$this->item->item_name,'price'=>new PriceResource($this->item->price ),'ItemsReservationRequirements'=>$this->item->ItemsReservationRequirements],
           //   'bill'=> ['id' =>$this->bill->id,'amount'=>$this->bill->amount ,'status'=>$this->bill->status,'status_id'=>$this->bill->status_id,'payment_method_id'=>$this->bill->payment_method_id],
          //  'feature'=>ReservationFeatureResource::collection($this->reservationFeatures),
            ];
    }
}
