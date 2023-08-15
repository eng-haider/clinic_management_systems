<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
           'address'=>new AddressResource($this->address),
           'full_name'=>$this->full_name,
           'user_email '=>$this->user_email,
           'status'=>$this->status,
           'user_phone'=>$this->user_phone,
           'image'=>$this->image

         ];
    }
}
