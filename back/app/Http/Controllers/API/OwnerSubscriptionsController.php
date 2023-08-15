<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\OwnerSubscriptions;
use Validator;
use App\SubscriptionPackages;
use App\Bills;
use App\Http\Resources\OwnerSubscriptionsCollection;

use App\Http\Services\ThirdPartyService;
use JWTAuth;
use App\Http\Resources\OwnerSubscriptionsResource;

class OwnerSubscriptionsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
     $OwnerSubscriptions=OwnerSubscriptions::orderBy('id','DESC')->paginate(15);
     return new OwnerSubscriptionsCollection($OwnerSubscriptions);
   
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function GetOwnerSubscriptions(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $ownerSubscriptions=OwnerSubscriptions::where('owner_id','=',$user->owner->id)->get()->last();

        
        return response()->json([
            'success'=>true,
            'data'=>new OwnerSubscriptionsResource($ownerSubscriptions)

        ]);
        
    }



    public function changeStatue(Request $request)
    {

        $validator = Validator::make($request->all(), [
          
            'state_id' =>'required|numeric',
            'OwnerSubscriptionId' =>'required|numeric',  
            
            
            ]);

            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'Data' =>  $validator->messages()
                ], 400);

                }
                $subscription = OwnerSubscriptions::find($request->OwnerSubscriptionId);
                if($request->state_id==29 && $subscription->remaining_reservations_number==null)
                {
                    
                    $subscription->remaining_reservations_number=$subscription->subscription_package->reservations_count; 

                }
              
                
                $subscription->status_id=$request->state_id; 
                $subscription->save();

                return response()->json([
                    'success' => true,
                    'data' =>null
                ], 200);

    }
    public function store(Request $request)
    {

        $user = JWTAuth::parseToken()->authenticate();
        $validator = Validator::make($request->all(), [
          
            'subscription_package_id' =>'required|numeric', 
            
            
            ]);

            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'Data' =>  $validator->messages()
                ], 400);

                }
                $SubscriptionPackages=SubscriptionPackages::find($request->subscription_package_id);

                $bill = new Bills();
                $bill->bill_id   = 11;
                $bill->user_id   = $user->id;
                $bill->amount    =$SubscriptionPackages->price_value;
                $bill->status_id = 32;
                $bill->payment_method_id=2;
                $bill->save();
			
                
                $tasded= (new ThirdPartyService)->createTasdidBill($user->full_name,$request->ownerPhone,$bill->amount,$bill->id,'2020-12-22T23:28:47.470Z');
                $b = Bills::find($bill->id);
                $b->save();


                $subscription = new OwnerSubscriptions;
                $subscription->owner_id = $user->owner->id;
                $subscription->subscription_package_id = $request->subscription_package_id;
                $subscription->status_id=28;
                $subscription->remaining_reservations_number=SubscriptionPackages::find($request->subscription_package_id)->reservations_count;
                $subscription->bill_id=$bill->id;
                $subscription->save();

                


            
                return response()->json([
                    'success' => true,
                    'data' =>null
                ], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $id=(int)$id;
        $subscription =OwnerSubscriptions::find($id);

        if($subscription->status_id !==28)
        {
            return response()->json([
                'success' => false,
                'Data' =>'This Packages Actived Can Not Update'
            ], 400);

        }
        else if($subscription->owner_id !== $user->owner->id)
        {
            return response()->json([
                'success' => false,
                'Data' =>''
            ], 400);

        }

        $validator = Validator::make($request->all(), [
          
            'subscription_package_id' =>'required|numeric', 
            
            
            ]);

            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'Data' =>  $validator->messages()
                ], 400);

                }
                $SubscriptionPackages=SubscriptionPackages::find($request->subscription_package_id);

                $bill = new Bills();
                $bill->bill_id   = 11;
                $bill->user_id   = $user->id;
                $bill->amount    =$SubscriptionPackages->price_value;
                $bill->status_id = 32;
                $bill->payment_method_id=2;
                $bill->save();
			
                
                $tasded= (new ThirdPartyService)->createTasdidBill($user->full_name,$request->ownerPhone,$bill->amount,$bill->id,'2020-12-22T23:28:47.470Z');
                $b = Bills::find($bill->id);
                $b->save();


                $subscription =OwnerSubscriptions::find($id);
                $subscription->owner_id = $user->owner->id;
                $subscription->subscription_package_id = $request->subscription_package_id;
                $subscription->status_id=28;
                $subscription->remaining_reservations_number=SubscriptionPackages::find($request->subscription_package_id)->reservations_count;
                $subscription->bill_id=$bill->id;
                $subscription->save();

                


            
                return response()->json([
                    'success' => true,
                    'data' =>null
                ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
