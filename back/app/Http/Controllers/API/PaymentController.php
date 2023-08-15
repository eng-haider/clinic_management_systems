<?php

namespace App\Http\Controllers;
use Illuminate\Routing\Controller;
use validator;
use App\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index()
    {
        return response()->json([
            'success' => true ,
            'data' => Payment::where('user_id','=', $request->auth->id)->get()
        ], 200);
    }
    public function store(Request $request)
    {
        $this->validate($request,[
         'payment_type_id' => 'required',
         'card_user_name' => 'required',
         'card_number' => 'required',
         'expiry_date' => 'required',
         'status_id' => 'required'
        ]);

        $payment = new Payment;
        $payment->user_id = $request->auth->id;
        $payment->payment_type_id = $request->payment_type_id;
        $payment->card_user_name = $request->card_user_name;
        $payment->card_number = $request->card_number;
        $payment->expiry_date = $request->expiry_date;
        $payment->payment_token = $request->payment_token;
        $payment->status_id = $request->status_id;
        $payment->save();

        return response()->json([
            'success' => true ,
            'data' => null
        ], 200);
    }
    public function update(Request $request)
    {
        $this->validate($request,[
            'payment_type_id' => 'required',
            'card_user_name' => 'required',
            'card_number' => 'required',
            'expiry_date' => 'required',
            'status_id' => 'required'
           ]);

           $payment = Payment::where(['user_id' => $request->auth->id,'card_number' => $request->card_number]);
           $payment->user_id = $request->auth->id;
           $payment->payment_type_id = $request->payment_type_id;
           $payment->card_user_name = $request->card_user_name;
           $payment->card_number = $request->card_number;
           $payment->expiry_date = $request->expiry_date;
           $payment->payment_token = $request->payment_token;
           $payment->status_id = $request->status_id;
           $payment->save();

           return response()->json([
               'success' => true ,
               'data' => null
           ], 200);
    }

    public function changePaymentStatus(Request $request){
        $this->validate($request,[
            'card_number' => 'required',
            'status_id' => 'required'
           ]);

           $payment = Payment::where(['user_id' => $request->auth->id,'card_number' => $request->card_number]);
           $payment->status_id = $request->status_id;
           $payment->save();

           return response()->json([
            'success' => true ,
            'data' => null
        ], 200);
    }

    public function destroy(Request $request)
    {
        $this->validate($request,[
            'card_number' => 'required'
           ]);
        Payment::where(['user_id' => $request->auth->id,'card_number' => $request->card_number])->delete();
        return response()->json([
            'success' => true ,
            'data' => null
        ], 200);
    }
}
