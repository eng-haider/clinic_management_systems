<?php

namespace App\Http\Controllers;
use Illuminate\Routing\Controller;
use Validator;
use App\Price;
use Illuminate\Http\Request;

class PriceController extends Controller
{


    public function update(Request $request, Price $price)
    {
        $this->validate($request,[
            'item_id' => 'required',
            'price_value' => 'required',
            'free_cancellation' => 'required',
            'deduction' => 'required',
            'no_prepayment' => 'required',
            'cancellation_deduction_ratio' => 'required'
        ]);

        $price = Price::find($request->item_id);
        $price->price_value = $request->price_value;
        $price->free_cancellation = $request->free_cancellation;
        $price->deduction = $request->deduction;
        $price->no_prepayment = $request->no_prepayment;
        $price->cancellation_deduction_ratio = $request->cancellation_deduction_ratio;
        $price->save();

        return response()->json([
            'success' => true,
            'data' => null
        ], 200);
    }

}
