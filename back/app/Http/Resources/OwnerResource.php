<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OwnerResource extends JsonResource
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
        'owner_barnd_name'=>$this->owner_barnd_name,
        'owner_email'=>$this->owner_email,
        'owner_phone'=>$this->owner_phone,
        'delivery_prices'=>$this->delivery_prices,
        'partnership'=>$this->partnership,
        'owner_description'=>$this->owner_description,
        'address'=>AddressResource::collection($this->address),
        'image'=>$this->image,
        'item_count'=>count($this->item),
        'CurrencyTransfers'=>$this->CurrencyTransfers,
      //  'CurrencyTransfers_dollar_price'=>$this->CurrencyTransfers->dollar_price,
        'owner_category'=>$this->ownerCategory,
        'user'=>new UserResource($this->user),
        'OwnerSubscriptions'=>$this->OwnerSubscriptions->last(),
        'created_at'=>$this->created_at->format('d-m-Y'),
        'status_id'=>$this->status_id,


      ];

    }


}
