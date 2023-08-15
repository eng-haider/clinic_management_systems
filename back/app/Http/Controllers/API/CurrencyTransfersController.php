<?php

namespace App\Http\Controllers\API;
use App\CurrencyTransfers;
use App\Owner;
use Validator;
use JWTAuth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CurrencyTransfersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }


    public function getByOwnerId()
    {
        $owner=Owner::where('user_id','=',JWTAuth::parseToken()->authenticate()->id)->first();
         $CurrencyTransfers =CurrencyTransfers::where('owner_id','=',$owner->id)->latest()->first();

        return response()->json([
            'success' => true,
            'Data' =>$CurrencyTransfers
        ], 200);

    }




    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
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
            'dollar_price' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'Data' =>  $validator->messages()
                ], 400);

                }


        $owner_id=Owner::where('user_id','=',JWTAuth::parseToken()->authenticate()->id)->get();
        // if($request->CurrencyTransfers_dollar_price!==null)
        //       {
                $CurrencyTransfers=new CurrencyTransfers;
                $CurrencyTransfers->owner_id= $owner_id[0]->id;
                $CurrencyTransfers->dollar_price=$request->dollar_price;
                $CurrencyTransfers->save();
            //   }
         
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
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'dollar_price' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'Data' =>  $validator->messages()
                ], 400);

                }

                $owner=Owner::where('user_id','=',JWTAuth::parseToken()->authenticate()->id)->first();
                $CurrencyTransfers =CurrencyTransfers::where('owner_id','=',$owner->id)->latest()->first();
              
                if($request->dollar_price !==  $CurrencyTransfers->dollar_price)
              {

          
                $CurrencyTransfers=new CurrencyTransfers;
                $CurrencyTransfers->owner_id= $owner->id;
                $CurrencyTransfers->dollar_price=$request->dollar_price;
                $CurrencyTransfers->save();


                
                
              }

              return response()->json([
                'success' => false,
                'Data' =>null
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
