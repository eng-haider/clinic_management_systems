<?php

namespace App\Http\Controllers\API;

use Validator;
use App\AdvertisingType;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class AdvertisingTypeController extends Controller
{


    public function __construct()
    {
      
       $this->middleware('CheckRole:admin')->only(['destroy','update','store']);
    }
    public function index()
    {

        $advertisings = AdvertisingType::all();

        return response()->json([
            'success' => true,
            'data' => $advertisings,
        ], 200);
    }

    public function show($id)
    {
        $id = (int) $id;
        $obj = AdvertisingType::find($id)->get();

        return response()->json([
            'success' => true,
            'data' => $obj,
        ], 200);
    }

    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'advertising_type_name' => 'required',
            'advertising_price' => 'required|numeric',
            'days_number' => 'required|numeric',
            'secondsـappearing_number' => 'required|numeric',
        ]);

    
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'Data' => $validator->messages(),
            ], 400);

        }

        $advertisingtype = new AdvertisingType;
        $advertisingtype->advertising_type_name = $request->advertising_type_name;
        $advertisingtype->advertising_price = $request->advertising_price;
        $advertisingtype->days_number = $request->days_number;
        $advertisingtype->secondsـappearing_number = $request->secondsـappearing_number;
        $advertisingtype->save();

        return response()->json([
            'success' => true,
            'data' => null,
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $id = (int) $id;
        $validator = Validator::make($request->all(), [
            'advertising_type_name' => 'required',
            'advertising_price' => 'required|numeric',
            'days_number' => 'required|numeric',
            'secondsـappearing_number' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'Data' => $validator->messages(),
            ], 400);

        }

        $advertisingtype = AdvertisingType::find($id);
        $advertisingtype->advertising_type_name = $request->advertising_type_name;
        $advertisingtype->advertising_price = $request->advertising_price;
        $advertisingtype->days_number = $request->days_number;
        $advertisingtype->secondsـappearing_number = $request->secondsـappearing_number;
        $advertisingtype->save();

        return response()->json([
            'success' => true,
            'data' => null,
        ], 200);
    }

    public function destroy($id)
    {

        $id = (int) $id;
        AdvertisingType::find($id)->delete();
        return response()->json([
            'success' => true,
            'data' => null,
        ], 200);
    }
}
