<?php

namespace App\Http\Controllers\API;

use App\Driver;
use App\User;
use Validator;
use JWTAuth;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Http\Resources\DriverResource;
use App\Http\Resources\DriverCollection;

class DriverController extends Controller
{

    public function index()
    {
  $user = JWTAuth::parseToken()->authenticate();

        $drivers = Driver
        //::with([
          //  'user:id,full_name,user_email,user_phone','user.address',

        //   'owner:id,owner_barnd_name,owner_email,owner_phone',
          //  'status:id,status_name',
    //    ])
        ::where('owner_delivery_id',$user->owner->id)
            ->orderBy('id', 'desc')
            ->paginate(15);
   $drivers= new DriverCollection($drivers);
        return response()->json([
            'success' => true,
            'data' => $drivers,
        ], 200);
    }

    public function store(Request $request)
    {
        // must be sign up before
        $validator = Validator::make($request->all(), [
            'user_phone' => 'required',
            'car_number' => 'required',
            'car_owner_name' => 'required',
            'driver_description' => 'required',
            'status_id' => 'required',

        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'data' => $validator->messages(),
            ], 400);
        }
        $user = JWTAuth::parseToken()->authenticate();
        $driver = new Driver();
        $var1=User::where('user_phone','=',$request->user_phone)->get();
        $driver->user_id =$var1[0]->id;
        $driver->owner_delivery_id = $user->owner->id;
        $driver->car_number = $request->car_number;
        $driver->car_owner_name = $request->car_owner_name;
        $driver->driver_description = $request->driver_description;
        $driver->status_id = $request->status_id;
        $driver->save();
        return response()->json([
            'success' => true,
            'data' => null,
        ], 200);
    }

    public function show($id)
    {
        $driver = Driver::with([
            'user:id,full_name,user_email,user_phone',
            'owner:id,owner_barnd_name,owner_email,owner_phone',
            'status:id,status_name',
        ])->where('id', $id)
            ->get();
        return response()->json([
            'success' => true,
            'data' => $driver,
        ], 200);
    }


    public function update(Request $request, Driver $driver)
    {
        $validator = Validator::make($request->all(), [
            'driver_id' => 'required|numeric',
            'user_phone' => 'required',
            'car_number' => 'required',
            'car_owner_name' => 'required',
            'driver_description' => 'required',
            'status_id' => 'required',

        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'data' => $validator->messages(),
            ], 400);
        }
        $driver = Driver::find($request->driver_id);
        $driver->user_id = User::where('user_phone', $request->user_phone)->get()->id;
        $driver->car_number = $request->car_number;
        $driver->car_owner_name = $request->car_owner_name;
        $driver->driver_description = $request->driver_description;
        $driver->status_id = $request->status_id;
        $driver->save();
        return response()->json([
            'success' => true,
            'data' => null,
        ], 200);
    }

    public function changeStatus(Request $request)
    {

        $driver = Driver::find($request->driver_id);
        $driver->status_id = $request->status_id;
        $driver->save();

        return response()->json([
            'success' => true,
            'data' => null,
        ], 200);
    }
}
