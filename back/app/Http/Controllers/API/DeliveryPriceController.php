<?php


namespace App\Http\Controllers\API;

use Validator;
use App\DeliveryPrice;
use JWTAuth;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class DeliveryPriceController extends Controller
{

    public function index()
    {
        $user = JWTAuth::parseToken()->authenticate();
        return response()->json([
            'success' => true,
            'data' => DeliveryPrice::where('owner_id', $user->owner->id)->with('province')->get(),
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'province_id' => 'required|numeric',
            'delivery_price_value' => 'required',
            'delivery_price_weight_kilos' => '',
            'delivery_prices_description' => '',

        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'data' => $validator->messages(),
            ], 400);
        }
        $user = JWTAuth::parseToken()->authenticate();

        $new = new DeliveryPrice();
        $new->owner_id = $user->owner->id;
        $new->province_id = $request->province_id;
        $new->delivery_price_weight_kilos= $request->delivery_price_weight_kilos;
        
        if ($request->delivery_price_value) {
            $new->delivery_price_value = $request->delivery_price_value;
        }

        if ($request->delivery_price_value) {
            $new->delivery_prices_description = $request->delivery_prices_description;
        }

        $new->save();
        return response()->json([
            'success' => true,
            'data' => null,
        ], 200);
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'deliveryPrice_id' => 'required|numeric',
            'province_id' => 'required|numeric',
            'delivery_price_value' => 'required',
            // 'delivery_price_weight_kilos' => '',
            // 'delivery_prices_description' => '',

        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'data' => $validator->messages(),
            ], 400);
        }

        $new = DeliveryPrice::find($request->deliveryPrice_id);
        $new->province_id = $request->province_id;
        $new->delivery_price_weight_kilos= $request->delivery_price_weight_kilos;
        if ($request->delivery_price_value) {
            $new->delivery_price_value = $request->delivery_price_value;
        }

        if ($request->delivery_price_value) {
            $new->delivery_prices_description = $request->delivery_prices_description;
        }

        $new->save();
        return response()->json([
            'success' => true,
            'data' => null,
        ], 200);
    }

    public function destroy($id)
    {

        $id=(int)$id;
        DeliveryPrice::find($id)->delete();
        return response()->json([
            'success' => true,
            'data' => null,
        ], 200);
    }
}
