<?php

namespace App\Http\Controllers;
use Illuminate\Routing\Controller;
use validator;
use App\PaymentType;
use Illuminate\Http\Request;

class PaymentTypeController extends Controller
{

    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => PaymentType::all()
        ], 200);
    }

    public function store(Request $request)
    {
        $this->validate($request,[
            'payment_type_name' => 'required'
        ]);
        $payment = new PaymentType;
        $payment->payment_type_name = $request->payment_type_name;
        $payment->save();

        return response()->json([
            'success' => true ,
            'date' => null
        ], 200);
    }

    public function update(Request $request, Payment $payment)
    {
        $this->validate($request,[
            'payment_type_id' => 'required',
            'payment_type_name' => 'required'
        ]);
        $payment = PaymentType::find($request->payment_type_id);
        $payment->payment_type_name = $request->payment_type_name;
        $payment->save();

        return response()->json([
            'success' => true ,
            'date' => null
        ], 200);
    }

}
