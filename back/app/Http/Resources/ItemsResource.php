<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ItemsResource extends JsonResource
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
            'owner_id'=>$this->owner_id ,
            'item_name'=>$this->item_name,
            'item_description'=>$this->item_description,
            'user'=>$this->user,
            'number_of_items'=>$this->number_of_items,
            'status'=>$this->status,
            'Address'=>AddressResource::collection($this->address),
            'sub_category'=> ['id' =>$this->SubCategory->id ,'name'=>$this->SubCategory->sub_category_name,'icon'=>$this->SubCategory->icon,'images'=>$this->SubCategory->images, 'category'=>['icon'=>$this->SubCategory->category->icon,'name'=>$this->SubCategory->category->category_name]], 
            'day_to_open'=>DayToOpenResource::collection($this->day_to_open),
            'ItemsReservationRequirements'=>ItemsReservationRequirementsResource::collection($this->ItemsReservationRequirements),
            'offer'=>OffersResource::collection($this->offer),
            'last_category_id'=>$this->last_category_id,
            'Advertising'=>AdvertisingsReasource::collection($this->advertise),
            'possib_reserving_period'=>$this->possib_reserving_period,
            'price'=>new PriceResource($this->price),
            'images'=>$this->image,
            'owner'=> ['id' =>$this->owner->id ,'owner_barnd_name'=>$this->owner->owner_barnd_name,'status_id'=>$this->owner->user->status_id], 
            'rate'=>$this->rate->avg('rate_value'),
            'ItemFeatures'=>$this->item_features,
            'every_day'=>$this->day_to_open[0]->every_day,
            'created_at'=>$this->created_at->format('d-m-Y')


            

        ];
    }

    public function with($request)
    {
            return ['success'=>'success'];
    }
}
