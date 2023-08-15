<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OwnerSubscriptionsResource extends JsonResource
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
            'owner'=>new OwnerResource($this->owner),
            'status'=>$this->status,
            'is_free'=>$this->is_free,
            'subscription_package'=>$this->subscription_package,
            'bill'=>new OwnerSubscriptionsBillResource($this->bill) ,
            'remaining_reservations_number'=>$this->remaining_reservations_number,
            'created_at'=>$this->created_at->format('d-m-Y')
            ];
    }
}
