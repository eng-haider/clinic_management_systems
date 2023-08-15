<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ItemsResourceUser extends JsonResource
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
            'status'=>$this->status,
            'item_description'=>$this->item_description,
            'owner'=> ['id' =>$this->owner->id ,'owner_barnd_name'=>$this->owner->owner_barnd_name],  
            'Address'=>AddressResource::collection($this->address),
            'sub_category'=> ['id' =>$this->SubCategory->id ,'name'=>$this->SubCategory->sub_category_name,'icon'=>$this->SubCategory->icon,'images'=>$this->SubCategory->images, 'category'=>['icon'=>$this->SubCategory->category->icon,'name'=>$this->SubCategory->category->category_name]], 
            'ItemsReservationRequirements'=>ItemsReservationRequirementsResource::collection($this->ItemsReservationRequirements),
          //  'offer'=> OffersResource::collection($this->offer->where('status_id', '=',19)->where('is_valid', '=',1)),
            'price'=>new PriceResource($this->price),
            'offer'=>OffersResource::collection( $this->offer->where('status_id', '=',19)->where('is_valid', '=',1))
           ,
            // 'last_category_id'=>$this->last_category_id,
            'images'=>$this->image,
            'Advertising'=>AdvertisingsReasource::collection($this->advertise),
            'rate'=>$this->rate->avg('rate_value'),
            'rate_user'=>RatingsResources::collection($this->rate),
            'possib_reserving_period'=>$this->possib_reserving_period,
            'ItemFeatures'=>$this->item_features,
            'day_to_open'=>DayToOpenResource::collection($this->day_to_open),
            'created_at'=>$this->created_at->format('d-m-Y'),
            'every_day'=>$this->day_to_open[0]->every_day,



        ];
    }

    public function with($request)
    {
            return ['success'=>'success'];
    }
}
