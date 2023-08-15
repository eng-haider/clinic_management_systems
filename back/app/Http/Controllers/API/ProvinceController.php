<?php

namespace App\Http\Controllers\API;
use Validator;
use App\Province;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ProvinceController extends Controller
{

    public function __construct()
    {
        $this->middleware('CheckRole:admin')->only(['destroy','update','store']);
    }

    public function index()
    {
        //
        $all = Province::all();
        return response()->json([
            'success' => true,
            'data' => $all
        ], 200);
    }

    public function store(Request $request)
    {


            $validator = Validator::make($request->all(), [
                'province_name' => 'required',
                'country_id' => 'required|numeric'
                ]);

                if ($validator->fails()) {
                    return response()->json([
                        'success' => false,
                        'Data' =>  $validator->messages()
                    ], 400);

                    }

         $province = new Province;
         $province->province_name = $request->province_name;
         $province->country_id = $request->country_id;
         $province->save();

         return response()->json([
             'success' => true,
             'data' => null
         ], 200);
    }


    public function show($id)
    {
        $province = Province::where('id', $id)->get();
        return response()->json([
            'success' => true,
            'data' => $province
        ], 200);
    }



    public function update(Request $request,$id)
    {
        $id=(int)$id;

        $validator = Validator::make($request->all(), [
            'province_name' => 'required',
            'country_id' => 'required|numeric'

            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'Data' =>  $validator->messages()
                ], 400);

                }


        $province = Province::find($id);
        $province->province_name = $request->province_name;
        $province->country_id = $request->country_id;
        $province->save();
        return response()->json([
            'success' => true,
            'data' => null
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Province  $province
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $id=(int)$id;
        $country = Province::find($id)->delete();
        return response()->json([
            'success' => true,
            'data' => null
        ], 200);
    }
}
