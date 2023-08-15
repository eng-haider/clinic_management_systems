<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OwnerSubscriptionsBillResource extends JsonResource
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
            'user_id'=>$this->user_id,
            'user'=>new UserResource($this->user),
            'bill_number'=>$this->bill_number,
            'bill_id'=>$this->bill_id,
            'payId'=>$this->payId,

            'amount'=>$this->amount,
            'status'=>$this->status,
            'status_id'=>$this->status_id,
            'podium_Deduction'=>$this->amount*0.02,
            'tasdid_bills'=>$this->tasdid_bills,
            'created_at'=>$this->created_at->format('d-m-Y')
        ];
    }
}
