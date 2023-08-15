<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OffersResource extends JsonResource
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
            'offer_name'=>$this->offer_name,
            'discount_percentage'=>$this->discount_percentage,
            'offer_from_date'=>$this->offer_from_date,
            'offer_to_date'=>$this->offer_to_date,
            'offer_description'=>$this->offer_description,
            'status_id'=>$this->status_id,
            'status'=>$this->status,
            'is_valid'=>$this->is_valid,
            'offer_price'=>$this->item->price->price_value-$this->item->price->price_value*($this->discount_percentage/100),
            'item'=> ['id' =>$this->item->id,'item_name'=>$this->item->item_name,'price'=>new PriceResource($this->item->price ),'ItemsReservationRequirements'=>$this->item->ItemsReservationRequirements],
        //   'item'=>new ItemsResource($this->item),
        ];
    }


    public function with($request)
    {
            return ['success'=>true];
    }

}
