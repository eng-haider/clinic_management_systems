<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\DB;
class ItemsSearchResource extends JsonResource
{
    /**
    
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {

    //     $user_id='';
    //     try {
           
    //     $user_id=JWTAuth::parseToken()->authenticate()->id;
    //     DB::commit();
    
    // }
    // catch (JWTException $e) {
    //   $user_id='not_found';
  
    
    
    // }
    if(count($this->image)>0)
    {
        $image=$this->image;
        
    }
    else
    {
        $image=null;
    }
    
        
        return
        [
            
           // 'ds'=>$this->sds,
            'id'=>$this->id,
            'owner_id'=>$this->owner_id ,
            'item_name'=>$this->item_name,
            'item_description'=>$this->item_description,
         //   'favorite'=>$user_id!=='not_found' ? count($this->favorite->where('user_id','=',$user_id)->where('favoriteable_id','=',$this->id))>0  ?true:false :false,
            'owner'=> ['id' =>$this->owner->id ,'owner_barnd_name'=>$this->owner->owner_barnd_name,'item_count'=>$this->owner->item->count()],    
            'Address'=>AddressResource::collection($this->address),
            'sub_category'=> ['id' =>$this->SubCategory->id ,'name'=>$this->SubCategory->sub_category_name,'icon'=>$this->SubCategory->icon,'images'=>$this->SubCategory->images, 'category'=>['icon'=>$this->SubCategory->category->icon,'name'=>$this->SubCategory->category->category_name]], 
            //'ItemsReservationRequirements'=>ItemsReservationRequirementsResource::collection($this->ItemsReservationRequirements),


            //'offer'=>['offer_name'=>$this->offer[0]->offer_name,'discount_percentage'=>$this->offer[0]->discount_percentage,'offer_from_date'=>$this->offer[0]->offer_from_date,'offer_to_date'=>$this->offer[0]->offer_to_date,'offer_description'=>$this->offer[0]->offer_description,'status_id'=>$this->offer[0]->status_id,'is_valid'=>$this->offer[0]->is_valid,'offer_price'=>$this->offer[0]->offer_price],
            'offer'=>OffersResource::collection($this->offer),
            'every_day'=>$this->day_to_open[0]->every_day,


            'rate'=>$this->rate->avg('rate_value'),
            'price'=>new PriceResource($this->price),
            'last_category_id'=>$this->last_category_id,
            'status'=>$this->status,
            'images'=> $image,
           // 'rate'=>$this->rate->avg('rate_value'),
            'rate_user'=>RatingsResources::collection($this->rate),
            'possib_reserving_period'=>$this->possib_reserving_period,
            'created_at'=>$this->created_at->format('d-m-Y')



        ];
    }

    public function with($request)
    {
            return ['success'=>'success'];
    }

}
