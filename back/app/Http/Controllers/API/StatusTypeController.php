<?php

namespace App\Http\Controllers\API;
use Validator;
use App\StatusType;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class StatusTypeController extends Controller
{

    public function __construct()
    {
        $this->middleware('CheckRole:admin')->only(['destroy','update','store']);
    }

    public function index()
    {
        //
        $all = StatusType::all();
        return response()->json([
            'success' => true,
            'data' => $all
        ], 200);
    }

    public function store(Request $request)
    {


            $validator = Validator::make($request->all(), [
                'StatusType_name' => 'required',
                ]);

                if ($validator->fails()) {
                    return response()->json([
                        'success' => false,
                        'Data' =>  $validator->messages()
                    ], 400);

                    }

         $StatusType = new StatusType;
         $StatusType->StatusType_name = $request->StatusType_name;
         $StatusType->save();

         return response()->json([
             'success' => true,
             'data' => null
         ], 200);
    }


    public function show($id)
    {
        $StatusType = StatusType::find($id);
        return response()->json([
            'success' => true,
            'data' => $StatusType
        ], 200);
    }



    public function update(Request $request,$id)
    {
        $id=(int)$id;

        $validator = Validator::make($request->all(), [
            'StatusType_name' => 'required',
            'country_id' => 'required|numeric'

            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'Data' =>  $validator->messages()
                ], 400);

                }


        $StatusType = StatusType::find($id);
        $StatusType->StatusType_name = $request->StatusType_name;
        $StatusType->save();
        return response()->json([
            'success' => true,
            'data' => null
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\StatusType  $StatusType
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $id=(int)$id;
        $country = StatusType::find($id)->delete();
        return response()->json([
            'success' => true,
            'data' => null
        ], 200);
    }
}
