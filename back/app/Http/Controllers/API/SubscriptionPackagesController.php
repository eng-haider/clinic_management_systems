<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\SubscriptionPackages;
use App\Owner;
use Validator;
use App\OwnerCategory;
use JWTAuth;

class SubscriptionPackagesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $SubscriptionPackages=SubscriptionPackages::get();
        return response()->json([
            'success' => false,
            'data' =>  $SubscriptionPackages
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'package_name' => 'required',
            'reservations_count' =>'required|numeric', 
            'price_value'=> 'required|numeric',
            ]);

            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'Data' =>  $validator->messages()
                ], 400);

                }

                $subscription = new SubscriptionPackages;
                $subscription->package_name = $request->package_name;
                $subscription->reservations_count = $request->reservations_count;
                $subscription->price_value = $request->price_value;

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

        $id=(int)$id;
        $validator = Validator::make($request->all(), [
            'package_name' => 'required',
            'reservations_count' =>'required|numeric', 
            'price_value'=> 'required|numeric',
            ]);

            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'Data' =>  $validator->messages()
                ], 400);

                }

                $subscription =SubscriptionPackages::find($id);
                $subscription->package_name = $request->package_name;
                $subscription->reservations_count = $request->reservations_count;
                $subscription->price_value = $request->price_value;

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
