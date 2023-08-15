<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ItemResourceOwner extends JsonResource
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
            'status'=>$this->status,
            'number_of_items'=>$this->number_of_items,
            // 'deliverable_id'=>$this->deliverable_id,
            'owner'=>new OwnerResource($this->owner),
            'Address'=>AddressResource::collection($this->address),
            'sub_category'=>new SubCategoryResource($this->SubCategory),
            'day_to_open'=>DayToOpenResource::collection($this->day_to_open),
            'ItemsReservationRequirements'=>ItemsReservationRequirementsResource::collection($this->ItemsReservationRequirements),
            'offer'=> OffersResource::collection($this->offer->where('status_id', '=',19)->where('is_valid', '=',1)),
            'price'=>new PriceResource($this->price),
            'last_category_id'=>$this->last_category_id,
            'images'=>$this->image,
            'Advertising'=>AdvertisingsReasource::collection($this->advertise),
            'rate'=>$this->rate->avg('rate_value'),
            'rate_user'=>RatingsResources::collection($this->rate),
            'possib_reserving_period'=>$this->possib_reserving_period,
            'ItemFeatures'=>$this->item_features,
            'created_at'=>$this->created_at->format('d-m-Y')



        ];
    }

    public function with($request)
    {
            return ['success'=>'success'];
    }
}
