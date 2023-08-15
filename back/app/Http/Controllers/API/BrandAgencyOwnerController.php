<?php

namespace App\Http\Controllers;

use App\BrandAgencyOwner;
use Illuminate\Http\Request;

class BrandAgencyOwnerController extends Controller
{

    public function index()
    {
        $brands = BrandAgencyOwner::with('brand', 'owner')
            ->orderBy('id', 'desc')
            ->paginate(15);
        return response()->json([
            'success' => true,
            'data' => $brands,
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'brand_id' => 'required|numeric',

        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'Data' => $validator->messages(),
            ], 400);

        }
        $user = JWTAuth::parseToken()->authenticate();
        $brandAgency = new BrandAgencyOwner;
        $brandAgency->brand_id = $request->brand_id;
        $brandAgency->owner_id = $user->owner->id;
        $brandAgency->save();

        return response()->json([
            'success' => true,
            'data' => null,
        ], 200);
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'brand_id' => 'required|numeric',
            'brandAgency_id' => 'required|numeric',

        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'Data' => $validator->messages(),
            ], 400);

        }
        $user = JWTAuth::parseToken()->authenticate();
        $brandAgency = BrandAgencyOwner::find($request->brandAgency_id);
        $brandAgency->brand_id = $request->brand_id;
        $brandAgency->owner_id = $user->owner->id;
        $brandAgency->save();

        return response()->json([
            'success' => true,
            'data' => null,
        ], 200);
    }


    public function destroy($brandAgency_id)
    {
        $brandAgency = BrandAgencyOwner::find($brandAgency_id)->delete();
    }
}
