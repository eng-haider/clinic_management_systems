<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BillsResource extends JsonResource
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
            'user'=>$this->user,
            //'reservation_id '=>$this->reservation_id ,
            'item_id'=>$this->item_id,
            'item'=>$this->item,
            'bill_number'=>$this->bill_number,
            'bill_id'=>$this->bill_id,
            'delivery_price_value'=>$this->delivery_price_value,
            'payId'=>$this->payId,
            'amount'=>$this->amount,
            'status'=>$this->status,
            'payment_method'=>$this->PaymentMethods,
            'status_id'=>$this->status_id,
            'podium_Deduction'=>$this->amount*0.02,
            'owner'=>new OwnerResource($this->item->owner),
            'tasdid_bills'=>$this->tasdid_bills,
            'created_at'=>$this->created_at->format('d-m-Y')
        ];
    }
}
