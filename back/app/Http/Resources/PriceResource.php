<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PriceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {

        if($this->is_dollars==2)
        {
            $price_dinar=$this->item->owner->CurrencyTransfers->dollar_price*$this->price_value;
            $price_dinar= $price_dinar*1000;
        }
        else if($this->is_dollars==1)
        {
            $price_dinar=$this->price_value;

        }
        return [

            'id'=>$this->id,
            'item'=>$this->item->owner->id,
            'price_value'=>$this->price_value,
            'is_dollars'=>$this->is_dollars,
            'price_dinar'=>$price_dinar,
            'free_cancellation'=>$this->free_cancellation,
            'payment_when_receiving'=>$this->payment_when_receiving,
            'is_zaincash_payment'=>$this->is_zaincash_payment,
            'is_tasdid_payment'=>$this->is_tasdid_payment,
            'installments'=>$this->installments,
            'deduction'=>$this->deduction,
            'cancellation_deduction_ratio'=>$this->cancellation_deduction_ratio, 
            'created_at'=>$this->created_at->format('d-m-Y')
          

            ];
    }
}
