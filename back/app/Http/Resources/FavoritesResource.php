<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FavoritesResource extends JsonResource
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
            'user'=>['id' =>$this->user->id ,'full_name'=>$this->user->full_name,'user_phone'=>$this->user->user_phone],
            'item'=>['id'=>$this->favoriteable->id,'item_name'=>$this->favoriteable->item_name]
            ];
    }
}
