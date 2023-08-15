<?php

namespace App\Http\Controllers\API;

use JWTAuth;
use App\Item;
use App\Bills;
use App\User;
use App\Offer;
use Validator;
use App\Driver;
use App\Status;
use Carbon\Carbon;
use App\Delivery;
use App\Owner;
use App\Partnership;
use App\Reservation;
use App\ItemFeatures;
use App\DeliveryPrice;
use App\DeliveryDriver;
use App\OwnerSubscriptions;
use App\ReservationFeature;
use App\ReservationRequirements;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use App\Http\Services\ThirdPartyService;
use App\Events\ReserverationAdded;
use App\Http\Controllers\API\ItemController;
use App\Http\Controllers\API\NotificationController;
use App\Http\Controllers\API\ReservationRequirementsController;
use App\Http\Controllers\UploadImageController;
use App\Http\Resources\ReservationCollection;
use App\Http\Resources\ReservationResource;
use App\Http\Resources\PriceResource;


class ReservationController extends Controller
{
    // get all reserv for owner
    public function index(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();

        $reservation = Reservation::WithRelated()
        ->whereHas('reservation.item',function($query) use($user){
            $query->where('owner_id',$user->owner->id);
         })->get();

        return new ReservationCollection($reservation);

    }


    //Get Reservation Delivery Price
    public function ReservationDeliveryPrice($item,$province_id)
    {
        $owner=$item->owner()->get();
        $Partnership=Partnership::where('owner_items_id','=',$owner[0]->id)->where('status_id','=',17)->get('owner_delivery_id');
        return  $delivery_price=DeliveryPrice::where('owner_id','=',$Partnership[0]->owner_delivery_id)->where('province_id','=',$province_id)->get();
       
      

    }
    // make reserve from client then create delivery request to delivery company
    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'item_id' => 'required|numeric',
            'deliverable' => 'required',
            'reservation_date' => 'required',
            'reservation_start_date' => 'required',
            'reservation_end_date' => 'required',
            'reservation_from_time' => 'required',
            'reservation_to_time' => 'required',
            'reservation_number' => 'required',
            'time_to_open_id' => 'numeric',
            'delivery_price_id' => 'exclude_if:deliverable,false|required|numeric',
            'province_id' => 'exclude_if:deliverable,false|required|numeric',
            'long' => 'exclude_if:deliverable,false|required',
            'lat' => 'exclude_if:deliverable,false|required',


        ]);



        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'data' => $validator->messages(),
            ], 400);
        }
        DB::beginTransaction();

        try {
                $item = Item::find($request->item_id);

                if(!$this->CheckOwnerSubscriptionsPackages($item->owner))
                {
                    return response()->json([
                        'success' => false,
                        'data' => 'SubscriptionsPackages Expire',
                    ], 400);

                }


                    if($item->owner->user->status_id !==1 )
                    {
                        return response()->json([
                            'success' => false,
                            'data' => null,
                        ], 400);
                    }
                
                
		
		
		
                   // event(new ReserverationAdded());

        $user = JWTAuth::parseToken()->authenticate(); 
        $reservation = new Reservation;
        $reservation->user_id = $user->id;
        $reservation->sub_time_id =$request->sub_time_id;
        $reservation->item_id = $request->item_id;
        $reservation->reservation_start_date = $request->reservation_start_date;
        if ($request->reservation_end_date) {
            $reservation->reservation_end_date = $request->reservation_end_date;
        }

        $reservation->reservation_from_time = $request->reservation_from_time;
        $reservation->reservation_to_time =$request->reservation_from_time;
        $reservation->reservation_number = $request->reservation_number;
        $numbers = range(11, 20);
        $reservation->serial_number = shuffle($numbers);
        $reservation->status_id = 4;
        $reservation->save();
        $amount = 0;

        if(count($request->item_features) > 0){
            for ($i=0; $i < count($request->item_features); $i++) {
                $a = ItemFeatures::find($request->item_features[$i]['id'])->where('item_id','=',$request->item_id)->first();

                $amount += $a->feature_price;
                $feature = new ReservationFeature();
                $feature->item_feature_id = $request->item_features[$i]['id'];
                $feature->reservation_id = $reservation->id;
                $feature->save();

            }

        }


            
    

            if($request->ReservationRequirements)
            {
    
               if(count($request->ReservationRequirements) > 0){
                for ($i=0; $i < count($request->ReservationRequirements); $i++) {
                $ReservationRequirements = new ReservationRequirementsController();
                $ReservationRequirements->store($request->ReservationRequirements[$i],$reservation->id);
            
                }
            }
        }



            

                if($item->price->is_dollars===2) { $price=$item->price->price_value*$item->owner->CurrencyTransfers->dollar_price ;$price=$price*1000; } else  {$price=$item->price->price_value;}  
                 /*Star Check if item have offers */
                 $offer =Offer::where('item_id','=',$item->id)->where('status_id', '=',19)->where('is_valid', '=',1)->get();
                 if(count($offer)==0)  {    }  else     { 
                if($item->price->is_dollars===2)
                {
                    $price=$item->owner->CurrencyTransfers->dollar_price-$item->owner->CurrencyTransfers->dollar_price*($offer[0]->discount_percentage/100);
                    $price=$price*1000;

                }
                else
                {
                    $price=$item->price->price_value-$item->price->price_value*($offer[0]->discount_percentage/100);

                }
               
            
            
            }
               
        

                
                
            $bill = new Bills();
            if($item->price->payment_when_receiving == 3){
                $bill->payment_method_id=1;
            }
            
            $bill->user_id = $user->id;
            $bill->item_id = $item->id;
            $bill->delivery_price_value=0;
            //$bill->delivery_price_id=$DeliveryPrice_id;
            $bill->amount =$price;
            $bill->reservation_id=$reservation->id;
            $bill->status_id =32;
            $bill->save();
            $this->SendOwnerNotification($reservation);
            DB::commit();
            return response()->json([
                'success' =>true,
                'all_amount' => $price+$amount,
                'res_id'=>$reservation->id,
                'feature_price'=>$amount
            ], 200);

        

        } catch (\Illuminate\database\QueryException $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'data'=>$e->getMessage()
               
            ], 400);

        }
        
       
        

       

       
    }












    //Send user Reserverations Notification To Owner
    public function SendOwnerNotification($reservation)
    {
        $notification=new NotificationController();
     
        $time = str_replace(['AM','PM'],['صباحاً',' مسائاً'],date('h:i A', strtotime($reservation->reservation_from_time)));
  
        

        $notifications= $reservation->Notification()->create(
            [
                'user_id' =>$reservation->item->owner->user->id,
                'notification_type_id' =>2,
                'notification_title'=>$reservation->item->item_name,
                'status_id'=>26,
                'notification_body'=>' يوجد حجز جديد على  '.$reservation->item->item_name.' من قبل الزبون '.$reservation->user->full_name.' الموعد '.$reservation->reservation_start_date.' الساعه '.$time    
            ]);
            if($notifications)
            {
            
                
                $send_data=['user'=>$reservation->item->owner->user,'notification_type_id'=>$notifications->notification_type_id,'notification_title'=>$reservation->item->item_name,'notification_body'=>' يوجد حجز جديد على  '.$reservation->item->item_name.' من قبل الزبون'.$reservation->user->full_name.'الموعد'.$reservation->reservation_start_date.' الساعه '.$time];
               $notification->sendPhoneNotification($send_data);
            }
        $reservation=new ReservationResource($reservation);

    }


    //store reserverations Owners

    public function storeOwner(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'item_id' => 'required|numeric',
            'reservation_date' => 'required',
            'user*.phone'=>'required',
            'reservation_start_date' => 'required',
            'reservation_end_date' => 'required',
            'reservation_from_time' => 'required',
            'reservation_to_time' => 'required',
            'reservation_number' => 'required',
            'time_to_open_id' => 'numeric',
            // 'province_id' => 'exclude_if:deliverable,false|required|numeric',
            // 'long' => 'exclude_if:deliverable,false|required',
            // 'lat' => 'exclude_if:deliverable,false|required',
            
              'province_id' => 'exclude_if:deliverable,false|numeric',
            'long' => 'exclude_if:deliverable,false',
            'lat' => 'exclude_if:deliverable,false',
            'delivery_description' => '',
            'item_features'=>''

        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'data' => $validator->messages(),
            ], 400);
        }
        $owner = JWTAuth::parseToken()->authenticate();
        $item = Item::where('id','=',$request->item_id)
        ->WhereHas('owner.user', function ($query) use( $owner)  {$query->where('id', '=', $owner->id);})
        ->first();

       
      

        // if($item->owner->user->status_id !==1 )
        // {
        //     return response()->json([
        //         'success' => true,
        //         'data' => null,
        //     ], 400);
        // }

        // else if(!$this->OwnerSubscriptionsPackages($owner->owner))
        // {
        //     return response()->json([
        //         'success' => false,
        //         'data' => 'SubscriptionsPackages Expire',
        //     ], 400);

        // }
        
        // if(User::where('user_phone','=',$request->user['phone'])->first())
        // {
    
        //     $user=User::where('user_phone','=',$request->user['phone'])->first();

        // }
        // else
        // {
            $user = new User;
            $user->full_name =$request->user['name'];
            $user->user_phone =$request->user['phone'];
            $user->role_id=1;
            $user->register_by_owner=1;
            $user->status_id=2;
            $user->save();

       // }


        

        
    
        
        $reservation = new Reservation;
        $reservation->user_id = $user->id;
        $reservation->status_id=4;
        
        $reservation->reservation_by_owner=1;
        $reservation->sub_time_id =$request->sub_time_id;
        $reservation->item_id = $request->item_id;
        if ($request->delivery_price_id) {
            $reservation->delivery_price_id = $request->delivery_price_id;
        }
        $reservation->reservation_start_date = $request->reservation_start_date;
        if ($request->reservation_end_date) {
            $reservation->reservation_end_date = $request->reservation_end_date;
        }

        $reservation->reservation_from_time = $request->reservation_from_time;
        
        $timestamp = strtotime($request->reservation_from_time) + 60*60;
        $to_time = date('H:i', $timestamp);
        
        
        // $reservation->reservation_to_time = $to_time;
        
        $reservation->reservation_to_time = $request->reservation_to_time;
        
        $reservation->reservation_number = $request->reservation_number;
        $numbers = range(11, 20);
        $reservation->serial_number = shuffle($numbers);
         $reservation->save();
        ///$reservation->status_id = 4;
       //$ReservationRequirementsCount=count($request->ReservationRequirements);
        // $items = count($request->item_features);
        // $amount = 0;

        // $feature_price=0;
        // if($items > 0){
        //     for ($i=0; $i < $items; $i++) {
        //         $a = ItemFeatures::find($request->item_features[$i]['id']);
        //         $feature_price += $a->feature_price;
        //     }

        // }



        try {
            DB::beginTransaction();
            $reservation->save();
            // if($items > 0){
            //     for ($i=0; $i < $items; $i++) {
            //     $feature = new ReservationFeature();
            //     $feature->item_feature_id = $request->item_features[$i]['id'];
            //     $feature->reservation_id = $reservation->id;
            //     $feature->save();
            //     }
            // }

        //     if($request->ReservationRequirements)
        //     {


                
        //     if(count($request->ReservationRequirements) > 0){
        //         for ($i=0; $i < count($request->ReservationRequirements); $i++) {
        //         $ReservationRequirements = new ReservationRequirementsController();
        //           $ReservationRequirements->store($request->ReservationRequirements[$i],$reservation->id);
            
                
            
        //         }
        //     }
        // }



            // if ($request->deliverable==1 ) {

            //     if($item->deliverable_id==2)
            //     {
            //         $DeliveryPrice=$this->ReservationDeliveryPrice($item,$request->province_id);
            //         $DeliveryPrice_value=$DeliveryPrice[0]->delivery_price_value;
            //         $DeliveryPrice_id=$DeliveryPrice[0]->id;
                    
            //     }
            //     else
            //     {
            //         $DeliveryPrice_value=0;
            //         $DeliveryPrice_id=null;
                    
            //     }
           
                
            //     $reservation->address()->create([
            //         'province_id' => $request->province_id,
            //         'long' => $request->long,
            //         'lat' => $request->lat,
            //         'address_descraption' => (!$request->address_descraption)?$request->address_descraption:'null',
            //     ]);

                
            //     $address=10;
                
            
            // }
            // else
            //     {
            //         $DeliveryPrice_value=0;
            //         $DeliveryPrice_id=null;
                    
            //     }


                // if($item->price->is_dollars===2) { $price=$item->price->price_value*$item->owner->CurrencyTransfers->dollar_price ;$price=$price*1000;} else  {$price=$item->price->price_value;}  
                // /*Star Check if item have offers */
                // $offer =Offer::where('item_id','=',$item->id)->where('status_id', '=',19)->where('is_valid', '=',1)->get();
                //  if(count($offer)==0)  {    }  else     { 
                //     if($item->price->is_dollars===2)
                //     {
                //         $price=$item->owner->CurrencyTransfers->dollar_price-$item->owner->CurrencyTransfers->dollar_price*($offer[0]->discount_percentage/100);
                //         $price=$price*1000;
    
                //     }
                //     else
                //     {
                //         $price=$item->price->price_value-$item->price->price_value*($offer[0]->discount_percentage/100);
    
                //     }
                   
                
                
             //   }

                // $podium_Deduction=0.02*$price;
                // $price=$podium_Deduction+$price+$feature_price;
              

                
	
		  
              //  if($item->price->payment_when_receiving == 3){
                    $bill = new Bills();
                    $bill->user_id = $user->id;
                    $bill->item_id = $item->id;
                    $bill->delivery_price_value=0;
                    $bill->delivery_price_id=null;
                   // $bill->podium_deduction=$podium_Deduction;
                    $bill->amount =$item->price->price_value;
                    $bill->reservation_id=$reservation->id;
                    $bill->status_id =32;
                    $bill->payment_method_id=1;
                    $bill->save();
                  //  }

            // $notification=new NotificationController();
            // $time = str_replace(['AM','PM'],['صباحاً',' مسائاً'],date('h:i A', strtotime($reservation->reservation_from_time)));
  
        // if($request->status_id==6)
        // {

        //   $notification_title='   تم قبول حجزك  في  '.$reservation->item->item_name.' موعد حجزك هو '.$reservation->reservation_start_date.' الساعه '.$time;
        //   $notifications= $reservation->Notification()->create(
        //       [
        //           'user_id' =>$user->id,
        //           'notification_type_id'=>3,
        //           'status_id'=>27,
        //           'sending_date'=>$reservation->reservation_start_date.' '.date('H:i',strtotime($reservation->reservation_from_time)-60*60),
        //           'notification_title'=>$reservation->item->item_name,
        //           'notification_body'=>'   متبقي ساعه لموعد حجزك في  '.$reservation->item->item_name.' موعد حجزك هو '.$reservation->reservation_start_date.' الساعه '.$time           
        //       ]);


               
            //   $send_data=['user'=>$reservation->user,'notification_type_id'=>3,'notification_title'=>$reservation->item->item_name,'notification_body'=>'  تم قبول حجزك موعد حجزك هو '.$reservation->reservation_start_date.' الساعه '.$time];
            //   $notification->sendPhoneNotification($send_data);
            
            //   $notifications= $reservation->Notification()->create(
            //     [
            //         'user_id' =>$user->id,
            //         'notification_type_id'=>1,
            //         'status_id'=>26,
            //         'notification_title'=>$reservation->item->item_name,
            //         'notification_body'=>$notification_title           
                 
            //     ]);
            //     if($notifications)
            //     {
            //      $notification->SendSms($notifications->id);
               
            //     }
               

        // }

            // $notifications= $reservation->Notification()->create(
            //     [
            //         'user_id' =>$user->id,
            //         'notification_type_id' =>1,
            //         'status_id' =>26,
            //         'notification_title'=>'تم قبول حجزك',
            //         'notification_body'=>' اهلا وسهلا بكم في منصه إحجز  إلي  تم قبول حجزك  في  '.$item->item_name.' موعد حجزك هو '.$reservation->reservation_start_date.' الساعه '.$time            
                 
            //     ]);
            //     if($notifications)
            //     {
            //         $notification->SendSms($notifications->id);
            //         $send_data=['user'=>$user,'data'=>$reservation,'notification_type_id'=>$notifications->notification_type_id,'notification_title'=>$notifications->notification_title,'notification_body'=>$notifications->notification_body];
            //         $notification->sendPhoneNotification($send_data);
            //     }

                



        //   if($request->withoutBills==1)
        //   {
        //     if($item->price->payment_when_receiving == 0){
        //         $response = (new ThirdPartyService)->createTasdidBill($user->full_name,$request->phone,intval($bill->amount),$bill->id,$request->reservation_end_date);             
        //          $b = Bills::find($bill->id);
        //          $b->payment_method_id=2;
        //          $b->save();
        //          DB::commit();
        //          return response()->json([
        //              'success' => true,
        //              'data' =>  $response,
        //          ], 400);
        //         }

        //         else if($item->price->payment_when_receiving == 1)
        //         {
        //             $b = Bills::find($bill->id);
        //             $b->payment_method_id=1;
        //             $b->save();

        //         }
        //   }
    

        //   else if($item->price->payment_when_receiving == 1)
        //   {
        //       $b = Bills::find($bill->id);
        //       $b->payment_method_id=1;
        //       $b->save();

        //   }

          
            // }

        
    }
    catch (\Illuminate\database\QueryException $e) {
             var_dump($e->errorInfo);
            DB::rollback();
            return response()->json([
                'success' => false,
               
            ], 400);

        }
        DB::commit();

        $reservation=new ReservationResource($reservation);

      
     //return   $notification->store($send_data);
        


        return response()->json([
            'success' =>true,
            // 'all_amount' => $price+$amount+$DeliveryPrice_value,
            // 'delivery_price'=>$DeliveryPrice_value,
            // 'feature_price'=>$amount
        ], 200);
    }




    // chenge reserv status by owner
    public function changeStatusByOwner(Request $request)
    {



        $ownerStatus = ['awaitingPayment', 'ownerConfirmed', 'ownerReject','complete'];
        $validator = Validator::make($request->all(), [
            'status_id' => 'required|numeric',
            'reservation_id' => 'required|numeric',
            'user_id'=>'required|numeric'

        ]);
        

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'data' => $validator->messages(),
            ], 400);
        }

    
      $succes=true;
    try {

        $status = Status::find($request->status_id);
       if (in_array($status->status_name, $ownerStatus)) {
        $user=User::find($request->user_id);

            $res = Reservation::where('id','=',$request->reservation_id)
            ->WhereHas('item.owner', function ($query)   {$query->where('id', '=',JWTAuth::parseToken()->authenticate()->owner->id);})->first() ;
           
           

            if($request->status_id==6)
            {
                $user = JWTAuth::parseToken()->authenticate(); 
                
                if($this->OwnerSubscriptionsPackages($user->owner))
                {
                    $res->status_id=$request->status_id;   
                    $res->owner_notes =$request->owner_notes;
                    $res->save();  

                }
                else
                {
                    return response()->json([
                        'success' => false,
                        'data' => 'SubscriptionsPackages Expire',
                    ], 400);

                }

            }

            else

            {
                $res->status_id=$request->status_id;   
                $res->owner_notes =$request->owner_notes;
                $res->save(); 
            }
            

            if(count($request->images)!==0)
         {
         $upload = new UploadImageController;
         $path = $upload->uploadInner($request);
         for($i = 0;$i < count($request->images);$i++){
        $res->image()->create(
         [
         'image_url' =>$path[$i],
         ]);
         }


       }
       }
        else
        {
            $succes=false;

        }


       // $res->save();     
    } catch (\Illuminate\Database\QueryException $e) {
        DB::rollback();
      
        return response()->json([
            'success' => false,
            'data' => 'Not Change',
        ], 400);
        $succes=false;
    }
    if($succes)
    {



       
        $this->SendNotification($request);
        

        return response()->json([
            'success' => true,
            'data' => 'sucess',
        ], 200);

 

    }
    else
    {
        return response()->json([
            'success' => false,
            'data' => null,
        ], 400);
        
    }

    
      

     
    }

    public function OwnerSubscriptionsPackages($owner)
    {
        $OwnerSubscriptions=OwnerSubscriptions::where('owner_id','=',$owner->id)->get()->last();
        if($OwnerSubscriptions->remaining_reservations_number > 0 && $OwnerSubscriptions->status_id == 29 && $OwnerSubscriptions->status_id !== 30)
        {

        $OwnerSubscriptions->remaining_reservations_number;
        if($OwnerSubscriptions->remaining_reservations_number===0)
        {
            $OwnerSubscriptions->status_id=30;

        }
        else if($OwnerSubscriptions->remaining_reservations_number>0)
        {
            $OwnerSubscriptions->remaining_reservations_number=$OwnerSubscriptions->remaining_reservations_number-1;

        }
        
        $OwnerSubscriptions->save();
        return true;


        }
        else

        {
           return false;
        }

    }



    public function CheckOwnerSubscriptionsPackages($owner)
    {
        $OwnerSubscriptions=OwnerSubscriptions::where('owner_id','=',$owner->id)->get()->last();
        if($OwnerSubscriptions->remaining_reservations_number > 0 && $OwnerSubscriptions->status_id == 29 && $OwnerSubscriptions->status_id !== 30)
        {

        $OwnerSubscriptions->remaining_reservations_number;
        if($OwnerSubscriptions->remaining_reservations_number===0)
        {
            $OwnerSubscriptions->status_id=30;

        }
        else if($OwnerSubscriptions->remaining_reservations_number>0)
        {
            // $OwnerSubscriptions->remaining_reservations_number=$OwnerSubscriptions->remaining_reservations_number-1;

        }
        
        $OwnerSubscriptions->save();
        return true;


        }
        else

        {
           return false;
        }

    }
    public function SendNotification($request)
    {

        $res = Reservation::find($request->reservation_id);

    
        $notification=new NotificationController();
        if(count($res->image)>0) {
        $img_url=$res->image[0]->image_url;
        $img_send='اضغط هنا لتحميل الصوره المرسله من الشركه '.'http://api.ahjez-ely.com/images/'.$img_url;      
        } else{
        $img_send='';}
       
       
        
        $time = str_replace(['AM','PM'],['صباحاً',' مسائاً'],date('h:i A', strtotime($res->reservation_from_time)));
  
        if($request->status_id==6)
        {

           $notification_title='   تم قبول حجزك  في  '.$res->item->item_name.' موعد حجزك هو '.$res->reservation_start_date.' الساعه '.$time .''.$img_send  ;
           $notifications= $res->Notification()->create(
               [
                   'user_id' =>$res->user->id,
                   'notification_type_id'=>3,
                   'status_id'=>27,
                   'sending_date'=>$res->reservation_start_date.' '.date('H:i',strtotime($res->reservation_from_time)-60*60),
                   'notification_title'=>$res->item->item_name,
                   'notification_body'=>'   متبقي ساعه لموعد حجزك في  '.$res->item->item_name.' موعد حجزك هو '.$res->reservation_start_date.' الساعه '.$time .''.$img_send          
               ]);

               $send_data=['user'=>$res->user,'notification_type_id'=>1,'notification_title'=>$res->item->item_name,'notification_body'=>'  تم قبول حجزك موعد حجزك هو '.$res->reservation_start_date.' الساعه '.$time .''.$img_send  ];
               $notification->sendPhoneNotification($send_data);
               

        }
        else if($request->status_id==9)
        {
           $notification_title='تم رفض حجز في'.$res->item->item_name;
           $send_data=['user'=>$res->user,'notification_type_id'=>1,'notification_title'=>$res->item->item_name,'notification_body'=>'  تم رفض حجزك  حجز  '.$img_send  ];
           $notification->sendPhoneNotification($send_data);
            
        }

        
        $notifications= $res->Notification()->create(
           [
               'user_id' =>$res->user->id,
               'notification_type_id'=>1,
               'status_id'=>26,
               'notification_title'=>$res->item->item_name,
               'notification_body'=>$notification_title           
            
           ]);
           if($notifications)
           {
            $notification->SendSms($notifications->id);
          
           }

    }

    // chenge reserv status by delivery
    public function changeStatusByDelivery(Request $request)
    {
        $deliveryStatus = ['InDelivery', 'UnderDelivery','Taking'];

        $validator = Validator::make($request->all(), [
            'status_id' => 'required|numeric',
            'reservation_id' => 'required|numeric',

        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'data' => $validator->messages(),
            ], 400);
        }
        $status = Status::find($request->status_id);
        if (in_array($status->status_name, $deliveryStatus)) {
            $res = Reservation::find($request->reservation_id);
            $res->status_id = $status->id;
            $res->save();
        }
        return response()->json([
            'success' => true,
            'data' => null,
        ], 200);
    }

    // chenge reserv status by user
    public function changeStatusByUser(Request $request)
    {


         $user=JWTAuth::parseToken()->authenticate();
      
        $userStatus = ['userCanceled'];
        $validator = Validator::make($request->all(), [
            'status_id' => 'required|numeric',
            'reservation_id' => 'required|numeric',

        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' =>true ,
                'data' => $validator->messages(),
            ], 400);
        }

                  
        $status = Status::find($request->status_id);
        if (in_array($status->status_name, $userStatus)) {
                      
            $res = Reservation::where('id','=',$request->reservation_id)->where('user_id','=',$user->id)->first();
            $res->status_id = $status->id;
            $res->save();
            return response()->json([
                'success' => true,
                'data' => null,
            ], 200);
        }

        else
        {
            return response()->json([
                'success' => false,
                'data' => 'not change',
            ], 400);

        }
       
    }
	
	
	
	
	
	
	public function changeStatusInDelivery(Request $request,$id)
	
	
    {
		$id=(int)$id;
        $status = Status::where("status_name","InDelivery")->get();
            $res = Reservation::find($id);
             $res->status_id = $status[0]->id;
            $res->save();
           // $item = Item::find($res->item_id);
          //  $item->number_of_items -= $number;
          //  $item->save();
        return response()->json([
            'success' => true,
            'data' => null,
        ], 200);
    }

    public function changeStatusUserDelivered($id,$number)
    {
		
        $status = Status::where("status_name","UserDelivered")->get();
            $res = Reservation::find($id);
             $res->status_id = $status[0]->id;
			 $res->reservation_number -= $number;
            $res->save();
			
            //$item = Item::find($res->item_id);
            //$item->number_of_items -= $number;
           // $item->save();
			
			
        return response()->json([
            'success' => true,
            'all_amount' => null,
        ], 200);
    }
	
	
	
	
    // chenge reserv status by driver
    public function changeStatusByDriver(Request $request)
    {
        $driverStatus = ['Taking', 'UserDelivered'];

        $validator = Validator::make($request->all(), [
            'status_id' => 'required|numeric',
            'reservation_id' => 'required|numeric',

        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'data' => $validator->messages(),
            ], 400);
        }
        $status = Status::find($request->status_id);
        if (in_array($status->status_name, $driverStatus)) {
            $res = Reservation::find($request->reservation_id);
            $res->status_id = $status->id;
            $res->save();
            $item = Item::find($res->item_id);
            $item->number_of_items += 1;
            $item->save();
        }
        return response()->json([
            'success' => true,
            'data' => null,
        ], 200);
    }
    // get all reserv between two dates for items owner
    // must set permation to this method **
    public function getBetweenDateOwner($owner_id, $first_date, $second_date)
    {
        $item = Reservation::WithRelated()
            ->OwnerItem($owner_id)
            ->GetBetweenDates($first_date, $second_date)
            ->paginate(15);
        return response()->json([
            'success' => true,
            'data' => $item,
        ], 200);
    }

    //get all user reservation
    public function getUserReservations(request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $item = Reservation::WithRelated()
            ->where("user_id", "=", $user->id)
            ->orderBy('id', 'desc')
            ->select('reservations.*', DB::raw("DATEDIFF(now(),reservation_end_date) as days_left"))
            ->paginate(15);
            return  new ReservationCollection($item);
    }
    // get all reservation user count not completed
    public function getUserReservationsCount(request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $item['count'] = Reservation::with('reservation.status', function ($query) {
            $query->where([
                ['status_name', '=', 'Binding'],
                ['status_name', '=', 'AwaitingPayment'],
                ['status_name', '=', 'OwnerConfirmed'],
                ['status_name', '=', 'InDelivery'],
                ['status_name', '=', 'TakeConfirmed'],
            ]);
        })->where('user_id', '=', $user->id)->where()->count();
        return response()->json([
            'success' => true,
            'data' => $item,
        ], 200);
    }
    // get all owner resrvation
    public function getOwnerReservations(request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $item = Reservation::WithRelated()
            ->WhereHas('item', function ($query) use ($user) {
                $query->where('owner_id', '=', $user->owner->id);
            })
            ->orderBy('id', 'desc')
           // ->groupBy('status_id')
            ->paginate(15);
            return  new ReservationCollection($item);
    }
// items owner counts for dashboard
    public function getOwnerDashboardCounts()
    {
        $user = JWTAuth::parseToken()->authenticate();
        $item = Item::where('owner_id', '=', $user->owner->id)->select('id')->get();
        $arr=[];
        foreach ($item as $i) {
            $arr[] = $i['id'];
        }
        $count = [
            'binding' => Reservation::GetStatusCount('Binding', $arr),
            'awaitingPayment' => Reservation::GetStatusCount('AwaitingPayment', $arr),
            'ownerConfirmed' => Reservation::GetStatusCount('OwnerConfirmed', $arr),
            'Taking' => Reservation::GetStatusCount('Taking', $arr),
            'InDelivery' => Reservation::GetStatusCount('InDelivery', $arr),
            'underDelivery' => Reservation::GetStatusCount('UnderDelivery', $arr),
            'userDelivered' => Reservation::GetStatusCount('UserDelivered', $arr),
            'userCanceled' => Reservation::GetStatusCount('UserCanceled', $arr),
            'ownerReject' => Reservation::GetStatusCount('OwnerReject', $arr),
        ];
        return response()->json([
            'success' => true,
            'data' => $count,
        ], 200);


    }

    public function getOwnerDashboardCountsDelivery()
    {
        $user = JWTAuth::parseToken()->authenticate();
        $count = [
            'OwnerConfirmed' => Delivery::with('reservation')->GetStatusCountDelivery('OwnerConfirmed', $user->owner->id),
            'Taking' => Delivery::with('reservation')->GetStatusCountDelivery('Taking', $user->owner->id),
            'InDelivery' => Delivery::with('reservation')->GetStatusCountDelivery('InDelivery', $user->owner->id),
            'UnderDelivery' => Delivery::with('reservation')->GetStatusCountDelivery('UnderDelivery', $user->owner->id),
            'UserDelivered' => Delivery::with('reservation')->GetStatusCountDelivery('UserDelivered', $user->owner->id),
            'UserCanceled' => Delivery::with('reservation')->GetStatusCountDelivery('UserCanceled', $user->owner->id),
            'OwnerReject' => Delivery::with('reservation')->GetStatusCountDelivery('OwnerReject', $user->owner->id),
        ];
        return response()->json([
            'success' => true,
            'data' => $count,
        ], 200);
    }
    // update reservation data by client
    public function update(Request $request, Reservation $reservation)
    {
        // $validator = Validator::make($request->all(), [
        //     'item_id' => 'required|numeric',
        //     'deliverable' => 'required',
        //     'reservation_date' => 'required',
        //     'delivery_price_id' => 'required|numeric',
        //     'reservation_start_date' => 'required',
        //     'reservation_end_date' => 'required',
        //     'reservation_from_time' => 'required',
        //     'reservation_to_time' => 'required',
        //     'reservation_number' => 'required',
        //     'delivery_price_id' => 'exclude_if:deliverable,false|required|numeric',
        //     'province_id' => 'exclude_if:deliverable,false|required|numeric',
        //     'long' => 'exclude_if:deliverable,false|required',
        //     'lat' => 'exclude_if:deliverable,false|required',
        //     'address_descraption' => '',

        // ]);

        // if ($validator->fails()) {
        //     return response()->json([
        //         'success' => false,
        //         'data' => $validator->messages(),
        //     ], 400);
        // }

        // $reservation = Reservation::find($request->reservation);
        // $reservation->item_id = $request->item_id;
        // if ($request->delivery_price_id) {
        //     $reservation->delivery_price_id = $request->delivery_price_id;
        // }

        // $reservation->reservation_start_date = $request->reservation_start_date;
        // if ($request->reservation_end_date) {
        //     $reservation->reservation_end_date = $request->reservation_end_date;
        // }

        // $reservation->reservation_from_time = $request->reservation_from_time;
        // $reservation->reservation_to_time = $request->reservation_to_time;
        // $reservation->reservation_number = $request->reservation_number;
        // $reservation->status_id = 1;

        // try {
        //     DB::beginTransaction();
        //     $reservation->save();
        //     if (Item::find($request->item_id)->deliverable) {
        //         $reservation->address()->update([
        //             'province_id' => $request->province_id,
        //             'long' => $request->long,
        //             'lat' => $request->lat,
        //             'address_descraption' => $request->address_descraption,
        //         ]);

        //     }

        // } catch (\Illuminate\database\QueryException $e) {
        //     // var_dump($e->errorInfo);
        //     DB::rollback();
        //     return response()->json([
        //         'success' => false,
        //         'data' => null,
        //     ], 400);

        // }
        // DB::commit();
        // return response()->json([
        //     'success' => true,
        //     'data' => null,
        // ], 200);
    }
    // get all reservation delivery request with filters in status of reserv
    // if status All get all otherwise send spicial status
    public function getReservToDelivaryOwner($status)
    {
        if ($status == 'All') {
            $statusArr = [
                ['status_name', '!=', 'Binding'],
                ['status_name', '!=', 'AwaitingPayment'],
            ];
        } else {
            $statusArr = [['status_name', '=', $status]];
        }
        $user = JWTAuth::parseToken()->authenticate();
        $reservations = Delivery::where('owner_delivery_id', '=', $user->owner->id)->select('reservation_id')->get();
        if (!$reservations) {
            return response()->json([
                'success' => false,
                'data' => 'bad opation',
            ], 400);
        }

        foreach ($reservations as $i) {
            $arr[] = $i['reservation_id'];
        }

//print_r(  $arr);
        $item = Reservation::whereIn('id', $arr)
            ->whereHas('status', function ($query) use ($statusArr) {
                $query->where($statusArr);
            })


            ->orderBy('id', 'desc')
            //->groupBy('status_id')
            ->paginate(15);
        return  new ReservationCollection($item);
    }
    // get all resrvations delivery for specific driver
    public function getReservToDriver($status)
    {
        if ($status == 'All') {
            $statusArr = [
                ['status_name', '=', 'InDelivery'],
                ['status_name', '=', 'OwnerConfirmed'],
            ];
        } else {
            if ($status != 'InDelivery' || $status != 'OwnerConfirmed') {
                return response()->json([
                    'success' => false,
                    'data' => 'bad opation',
                ], 400);
            }
            $statusArr = [['status_name', '=', $status]];
        }

        $user = JWTAuth::parseToken()->authenticate();
        $driver = Driver::where('user_id', $user->id)->first();

        $driverData = DeliveryDriver::with('delivery', 'delivery.reservation.status', 'delivery.reservation')
            ->whereHas('delivery.reservation.status', function ($query) use ($status) {
                $query->where('status_name', '=', $statusArr);
            })->where('driver_id', $driver->id)
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        return response()->json([
            'sucess' => true,
            'data' => $driverData,
        ], 200);
    }



    public function OwnerResCount(request $request)
    {

        $user = JWTAuth::parseToken()->authenticate();
        $owner_id=$user->owner->id;
        $reservation = Reservation::WhereHas('item', function ($query) use ($owner_id) {
            $query->where('owner_id', '=', $owner_id);
        })
        ->count();

        return response()->json([
            'sucess' => true,
            'data' =>$reservation,
        ], 200);

    }


    public function SearchOwner(request $request)
    {

        $user = JWTAuth::parseToken()->authenticate();
        $owner_id=$user->owner->id;
        $reservation = QueryBuilder::for(Reservation::class)
        ->WhereHas('item', function ($query) use ($owner_id) {
            $query->where('owner_id', '=', $owner_id);
        })

        ->allowedSorts('id')
        ->allowedFilters(['id','item_id','status_id','user.user_phone','user.full_name',AllowedFilter::scope('BetweenDate')])
        ->paginate(15);

       return new ReservationCollection($reservation);

    }



    public function SearchAvilableReservation(request $request)
    {


        $reservation = QueryBuilder::for(Reservation::class)
        ->allowedSorts('id')
        ->allowedFilters(['id','item_id','status_id','reservation_start_date',AllowedFilter::scope('BetweenDate'),AllowedFilter::scope('BetweenTime')])
        ->get();
        

       return new ReservationCollection($reservation);

    }



    public function SearchUser(request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $reservation = QueryBuilder::for(Reservation::class)
                ->where('user_id', '=', $user->id)
                ->allowedSorts('id')
                ->allowedFilters(['id', 'item_id', 'status_id', AllowedFilter::scope('BetweenDate')])
                ->orderBy('id', 'desc')
                ->paginate(10);

        return new ReservationCollection($reservation);
    }
  
  
  
    public function destroy($id)
    {

        $owner=Reservation::where('id','=',$id)->first();
        // $Items=Item::where('id','=',$id)->where('owner_id','=',$owner->id)->first();

        // $Items->status_id=25;
        // $Items->save();
        
        $owner->delete();

        return response()->json([
            'success' => true,
            'Data' => null
        ], 200);



    }
    
    
    public function GetAvilableReservationPeriod(request $request)
    {

        $res=  Reservation::where('reservation_start_date','=',$request->date)
        //->where('status_id','=',6)
        ->get()
        ->pluck('sub_time_id');

        $c = new Carbon($request->date);
        $weekday=$c->dayOfWeek; 
         $day_res=$request->date;
         $item=item::where('id','=',$request->item_id)->with(['day_to_open'=>function($query) use($weekday, $res,$day_res){
                $query->where('work_day',$weekday)->with(['time_to_open'=>function($query) use($weekday,$res,$day_res)
                {
                 $query->with(['SubTime'
                 =>function($query) use($day_res) {
                    return  $query->with(['reservation'
                    =>function($query) use($day_res){
                        return $query
                       ->whereIn('status_id',[4,6])
                        ->where('reservation_start_date','=',$day_res)                   
                        ->get();
                    }
                    
                    
                    ])
                    ->get();
                    
                 }
                 ]
                 
                );
                
                },
            
             ]
             
            
            );
           
                }])->get()->pluck('day_to_open');

                $time_open=json_decode($item);
                if(count($time_open)==0)
                {
                    return response()->json([
                        'sucess' => true,
                        'data' => 'not found',
                    ], 200);

                }
                else
                {
                   
                 $time_open=  $time_open[0][0]->time_to_open;
                

                return response()->json([
                    'sucess' => true,
                    'data' => $time_open,
                ], 200);

                }
                
                



    }

}
