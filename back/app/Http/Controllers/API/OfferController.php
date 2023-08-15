<?php

namespace App\Http\Controllers\API;
use Validator;
use App\Offer;
use App\Favorite;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Reservation;
use Carbon\Carbon;
use App\Item;
use App\Address;
use App\User;
use App\Owner;
use JWTAuth;
use App\Notification;
use App\Http\Resources\OffersResource;
use App\Http\Resources\OffersCollection;
use Illuminate\Support\Facades\DB;

class OfferController extends Controller
{
    public function __construct()
    {
      $this->middleware('CheckRole:owner')->only(['destroy','update','store']);
    }


    public function index()
    {

        $offers = Offer::orderBy('offer_from_date','DESC')->paginate('10')->where('status_id','=',19);
        return new OffersCollection($offers);



    }


    public function ChangeOffersStatus()
    {


        // $Reservation_more = Offer::
        // whereTime('from_time', '=', Carbon::now('Asia/Baghdad')->addMinutes(1);
        // ->toTimeString())
        // ->whereDate('offer_to_date', '=', Carbon::now()
        // ->toDateString())->get();
        // foreach($Reservation_more as $Reser)
        //     {
             
        //         $Reser->is_valid=1;
        //         $Reser->save();
        //     }



        $Reservation_more = Offer::
        whereTime('to_time', '<', Carbon::now('Asia/Baghdad')
        ->toTimeString())
        ->whereDate('offer_to_date', '=', Carbon::now()
        ->toDateString())->get();

        foreach($Reservation_more as $Reser)
            {
             
                $Reser->is_valid=0;
                $Reser->save();
            }

            


     

      $Reservation_less = Offer::whereDate('offer_to_date', '<', Carbon::now()->toDateString())->get();
       foreach($Reservation_less as $Reser)
       {
         
           $Reser->is_valid=0;
           $Reser->save();
       }




   $Reservation_more = Offer::whereDate('offer_to_date', '>', Carbon::now()->toDateString())->get();
   foreach($Reservation_more as $Reser)
       {
        
           $Reser->is_valid=1;
           $Reser->save();
       }







       $Reservation_less = Offer::whereDate('offer_from_date', '>', Carbon::now()->toDateString())->get();
       foreach($Reservation_less as $Reser)
       {
        
          
           $Reser->is_valid=0;
           $Reser->save();
       }



      $now_date=date("Y-m-d");
      return response()->json([
       'success' => true,
       'Data' =>'',

   ], 200);



    }

    public function store(Request $request)
    {



            $validator = Validator::make($request->all(), [
                'offer_name' => 'required',
                'old_price' => 'required|numeric',
                'item_id' => 'required|numeric',
                'from_time' => 'time',
                'to_time' => 'time',
                'discount_percentage' => 'required|numeric',
                'offer_from_date' => 'required|date_format:Y-m-d',
                'offer_to_date' => 'required|date_format:Y-m-d'
                    ]);


                    if ($validator->fails()) {
                        return response()->json([
                            'success' => false,
                            'Data' =>  $validator->messages()
                        ], 400);

                        }

                        $offer_price = $request->old_price- $request->old_price * ($request->discount_percentage / 100);
        try{
          
            $owner=Owner::where('user_id','=',JWTAuth::parseToken()->authenticate()->id)->first();
            $item=Item::find($request->item_id)->where('owner_id','=',$owner->id)->first();


            $offer = new Offer;
            $offer->offer_name = $request->offer_name;
            $offer->item_id = $request->item_id;
            $offer->discount_percentage = $request->discount_percentage;
            $offer->offer_price = $offer_price;
            $offer->offer_from_date = $request->offer_from_date;
            $offer->offer_to_date = $request->offer_to_date;
            $offer->status_id = $request->status_id;
            $offer->from_time = Carbon::now('Asia/Baghdad')->addMinutes(1)
             ->toTimeString();
            $offer->offer_description = $request->offer_description;
            $offer->is_valid=1;
            // if($request->from_time==$request->to_time)
            // {
            //     $offer->from_time = $request->from_time;
            //     $offer->to_time = $request->to_time;

            // }
           
            if($item)
            {
            $offer->save();
            $Data = $offer->id;
            }
            else
            {
                return response()->json([
                    'success' => false,
                    'Data' =>null
                ], 400);  
            }
            

        }catch(\Illuminate\Database\QueryException $e){
            dd($e->getMessage());

            $Data = null;
        }
        if(empty($Data)){
            return response()->json([
                'success' => false,
                'Data' => null
            ], 400);
        }else{

            
          // $this->SendNotifications($offer);
         
            return response()->json([
                'success' => true,
                'id' => $offer->id
            ], 200);
        }
    }

    public function SendNotificationsToNearUser($offer)
    {
       
        $lat=$offer->item->address[0]->lat;
        $long=$offer->item->address[0]->long;

     

       return $UserNear=Address::whereHasMorph('addressable',[User::class])
                    ->with('addressable')
                    ->select('addresses.*',DB::raw("6371 * acos(cos(radians('$lat'))   * cos(radians(addresses.lat))  * cos(radians(addresses.long) - radians('$long')) + sin(radians('$lat'))* sin(radians(addresses.lat))) AS distance")  )
                    ->having('distance', '<', 40)
                    ->orderBy('distance','DESC')
                    ->get(); 

        foreach($UserNear as $user)
        {
            $Notification = Notification::
    
                orwhereHasMorph('notificationable', [Offer::class])
                ->where('notification_type_id','=',$offer->id)
                ->where('user_id','=',$user->addressable->id)
                ->get();

                if(count($Notification)==0)
                {
                    $notifications= $offer->Notification()->create(
                        [
                            'user_id' =>$user->addressable->id,
                            'notification_type_id'=>6,
                            'status_id'=>27,
                            'sending_date'=>$offer->offer_from_date.' '.date('H:i',strtotime('09:00:00')-60*60),
                            'notification_title'=>$offer->item->item_name,
                            'notification_body'=>$offer->offer_name      
                        ]);
        

                }
           

        } 

    }

    public function SendNotifications($offer)
    {

         $items=Favorite::whereHasMorph('favoriteable',[Item::class])
        ->with('favoriteable')
        ->get();
        
        
        foreach($items as $item)
        {
         
            $notifications= $offer->Notification()->create(
                [
                    'user_id' =>$item->user_id,
                    'notification_type_id'=>6,
                    'status_id'=>27,
                    'sending_date'=>$offer->offer_from_date.' '.date('H:i',strtotime($offer->from_time)-60*60),
                    'notification_title'=>$item->favoriteable->item_name,
                    'notification_body'=>$offer->offer_name      
                ]);


        }

      $this->SendNotificationsToNearUser($offer);
        


    }

    public function show($id)
    {

        $offers = Offer::where('id','=',$id)->first();
        return new offersResource($offers);

    }


    public function getOwnerOffers(Request $request){


       

            $offers = Offer::orderBy('offer_from_date','DESC')->paginate('10')->where('item.owner_id','=',JWTAuth::parseToken()->authenticate()->owner->id);
            return new OffersCollection($offers);
          
      
    }


    public function update(Request $request,$id)
    {

        $id=(int)$id;

        $this->ChangeOffersStatus();
        $this->ChangeOffersStatus();

        $validator = Validator::make($request->all(), [
            'offer_name' => 'required',
            'old_price' => 'required|numeric',
           // 'item_id' => 'required|numeric',
            'discount_percentage' => 'required|numeric',
            'offer_description' => 'required',
            'offer_from_date' => 'required|date_format:Y-m-d',
            'offer_to_date' => 'required|date_format:Y-m-d'
                ]);

                if ($validator->fails()) {
                    return response()->json([
                        'success' => false,
                        'Data' =>  $validator->messages()
                    ], 400);

                    }


                    $offer_price = $request->old_price- $request->old_price * ($request->discount_percentage / 100);
        try{

            
          $owner=Owner::where('user_id','=',JWTAuth::parseToken()->authenticate()->id)->first();
           $offer=Offer::where('id','=',$id)
            ->WhereHas('item.owner', function ($query) use($owner)  {$query->where('id', '=',$owner->id);})
            ->first();

            $offer->offer_name =$request->offer_name;
           // $offer->item_id = $request->item_id;
            $offer->discount_percentage = $request->discount_percentage;
            $offer->offer_price = $offer_price;
            $offer->offer_from_date = $request->offer_from_date;
            $offer->offer_to_date = $request->offer_to_date;
            $offer->status_id = $request->status_id;
            $offer->offer_description = $request->offer_description;
            $offer->save();
            $Data = $offer->id;
            $this->ChangeOffersStatus();

        }catch(\Illuminate\Database\QueryException $e){
            dd($e->getMessage());

            $Data = null;
        }
        if(empty($Data)){
            return response()->json([
                'success' => false,
                'Data' => null
            ], 400);
        }else{
            return response()->json([
                'success' => true,
                'id' => $offer->id
            ], 200);
        }
    }






    public function destroy(Request $request,$id)
    {
        $owner=Owner::where('user_id','=',JWTAuth::parseToken()->authenticate()->id)->first();
        $Offer=Offer::where('id','=',$id)
        ->WhereHas('item.owner', function ($query) use($owner)  {$query->where('id', '=',$owner->id);})
        ->first();

        if($Offer)
        {
            $Offer->delete();
        return response()->json([
            'success' => true,
            'Data' => null
        ], 200);

        }
        else
        {
            return response()->json([
                'success' => false,
                'Data' => null
            ], 400);  
        }
       

       
    }
}
