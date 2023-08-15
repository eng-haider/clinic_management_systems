<?php

namespace App\Http\Controllers\API;
use JWTAuth;
use Validator;
use App\Driver;
use App\Delivery;
use App\DeliveryDriver;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class DeliveryController extends Controller
{

    public function index()
    {
        // get all driver deliveries
        $user = JWTAuth::parseToken()->authenticate();
        $driver = Driver::where('user_id',$user->id)->first();

        $driverData = DeliveryDriver::with('delivery','delivery.reservation.status','delivery.reservation')
        ->whereHas('delivery.reservation.status',function($query){
          $query->where([
            ['status_name', '!=', 'OwnerConfirmed'],
            ['status_name', '!=', 'TakeDelivery'],
            ['status_name', '!=', 'InDelivery']
            ]);
        })->where('driver_id', $driver->id)
        ->orderBy('created_at', 'desc')
        ->paginate(15);
        return response()->json([
            'sucess' => true ,
            'data' => $deliveries
        ], 200);
    }


    public function getTacking(){
           // get all driver deliveries
           $user = JWTAuth::parseToken()->authenticate();
           $driver = Driver::where('user_id',$user->id)->first();

           $driverData = DeliveryDriver::with('delivery','delivery.reservation.status','delivery.reservation','delivery.reservation.item')
           ->whereHas('delivery.reservation.status',function($query){
           $query->where('status_name' , 'Taking');
           })->where('driver_id', $driver->id)
           ->orderBy('created_at', 'desc')
           ->paginate(15);
           return response()->json([
               'sucess' => true ,
               'data' => $driverData
           ], 200);
       }

        public function getUnderDelivery(){
            // get all driver deliveries
            $user = JWTAuth::parseToken()->authenticate();
            $driver = Driver::where('user_id',$user->id)->first();

            $driverData = DeliveryDriver::with('delivery','delivery.reservation.status','delivery.reservation','delivery.reservation.item')
            ->whereHas('delivery.reservation.status',function($query){
            $query->where('status_name' , 'UnderDelivery');
            })->where('driver_id', $driver->id)
            ->orderBy('created_at', 'desc')
            ->paginate(15);
            return response()->json([
                'sucess' => true ,
                'data' => $driverData
            ], 200);
        }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'driver_id' => 'required|numeric',
            'delivery_id' => 'required|numeric',
            'status_id'  => 'required|numeric',
        ]);
        if($validator->fails()){
            return response()->json([
                'sucess' => false ,
                'data' => null
            ], 400);
        }
         $delivery = new DeliveryDriver;
         $delivery->driver_id = $request->driver_id;
         $delivery->delivery_id = $request->delivery_id;
         $delivery->status_id = $request->delivery_prices_id;
         $delivery->save();
        return response()->json([
            'sucess' => true ,
            'data' => null
        ], 200);
    }


    public function show($owner,$id)
    {
        $delivery = null;
        if($owner == 'delivery'){
            $delivery = Delivery::with(['items','delivery_prices','delivery_prices.provinces'])->where('item_delivery_id',$id)
            ->get();
        }else if($owner == 'owner'){
            $delivery = Delivery::with(['items','delivery_prices','delivery_prices.provinces'])->where('owner_item_id',$id)
            ->get();
        }
        return response()->json([
            'sucess' => true ,
            'data' => $delivery
        ], 200);
    }

    public function getCount()
    {
        //
    }


    public function update(Request $request)
    {

        $validator = Validator::make($request->all(),[
            'delivery_id' => 'required|numeric',
            'item_delivery_id' => 'required|numeric',
            'owner_item_id' => 'required|numeric',
            'user_id'  => $request->auth->id,
            'delivery_prices_id' => 'required|numeric',
            'item_id'  => 'required|numeric',
            'delivery_description'  => '',
            'delivery_time' => ''
        ]);
        $data = $request->validate([
            'delivery_id' => 'required|numeric',
            'item_delivery_id' => 'required|numeric',
            'owner_item_id' => 'required|numeric',
            'user_id'  => $request->auth->id,
            'delivery_prices_id' => 'required|numeric',
            'item_id'  => 'required|numeric',
            'delivery_description'  => '',
            'delivery_time' => ''
        ]);

        $delivery = Delivery::find($data);
        $delivery->update($data);

        return response()->json([
            'sucess' => true ,
            'data' => null
        ], 200);
    }


    public function destroy(Delivery $delivery)
    {
        //
    }
}
