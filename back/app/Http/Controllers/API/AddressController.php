<?php

namespace App\Http\Controllers\API;

use App\Address;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Owner;
use App\Item;
use JWTAuth;
use Validator;

class AddressController extends Controller
{

    public function index()
    {

        $all = Address::all();
        return response()->json([
            'success' => true,
            'data' => $all,
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'province_id' => 'required|numeric',
            'long' => 'required',
            'lat' => 'required',
            'address_descraption' => 'alpha_dash',

        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'Data' => $validator->messages(),
            ], 400);

        }

        $address = new Address;
        $address->province_id = $request->province_id;
        $address->long = $request->long;
        $address->lat = $request->lat;
        $address->address_descraption = $request->address_descraption;
        $address->save();

        return response()->json(array('success' => true), 200);
    }

    public function show($id)
    {
        $address = Address::find($id);

        return response()->json([
            'success' => true,
            'data' => $address,
        ], 200);
    }

    public function update(Request $request, $id)
    {

        $id = (int) $id;
        $validator = Validator::make($request->all(), [
            'province_id' => 'required|numeric',
            'long' => 'required',
            'lat' => 'required',
            'address_descraption' => 'alpha_dash',

        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'Data' => $validator->messages(),
            ], 400);

        }
        $owner=Owner::where('user_id','=',JWTAuth::parseToken()->authenticate()->id )->first();
        $address = Address::where('id','=',$id)->where('addressable_id','=',$owner->id)->first();
        $address->province_id = $request->province_id;
        $address->long = $request->long;
        $address->lat = $request->lat;
        $address->address_descraption = $request->address_descraption;
        $address->save();

        return response()->json([
            'success' => true,
            'data' => null,
        ], 200);
    }

    public function destroy($id)
    {

        
        $id = (int) $id;
        $owner=Owner::where('user_id','=',JWTAuth::parseToken()->authenticate()->id)->first();
        $address = Address::where('id','=',$id)->where('addressable_id','=',$owner->id)->first();
        
        
        if(count($address->ItemsAddresses)>0)
        {
            foreach($address->ItemsAddresses as $item)
        {
            if($item->status_id==11)
            {
                return response()->json([
                    'success' => false,
                    'data' =>'have item',
                ], 400);
                break;
    

            }
            else{
                $address->delete();
                return response()->json([
                    'success' => true,
                    'data' => null,
                ], 200);
    
            }
            

        }
          
        }
        else{
            $address->delete();
            return response()->json([
                'success' => true,
                'data' => null,
            ], 200);

        }
        
        
    }

}
