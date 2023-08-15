<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\ItemsReservationRequirements;
use Validator;
use App\Item;
use App\Owner;
use App\ItemFeatures;
use JWTAuth;
class ItemsReservationRequirementsController extends Controller
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
            'item_id' => 'required|numeric',
            'requirement_name' => 'required',
            ]);


            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'Data' =>  $validator->messages()
                ], 400);

                }
                $owner=Owner::where('user_id','=',JWTAuth::parseToken()->authenticate()->id)->first();
                $item=Item::find($request->item_id)->where('owner_id','=',$owner->id)->first();
                if($item)
                {
            
                }
                else
                {
                    return response()->json([
                        'success' => false,
                        'Data' => null
                    ], 400);  
                }
    
        $ItemsReservationRequirements = new ItemsReservationRequirements;
        $ItemsReservationRequirements->item_id = $request->item_id;
        $ItemsReservationRequirements->requirement_name = $request->requirement_name;
        $ItemsReservationRequirements->save();

        return response()->json([
            'success' => true,
            'data' => null
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
        $validator = Validator::make($request->all(), [
          //  'item_id' => 'required|numeric',
            'requirement_name' => 'required',
            ]);


            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'Data' =>  $validator->messages()
                ], 400);

                }
               
        $ItemsReservationRequirements =ItemsReservationRequirements::find($id);
      //  $ItemsReservationRequirements->item_id = $request->item_id;
        $ItemsReservationRequirements->requirement_name = $request->requirement_name;
        

        $owner=Owner::where('user_id','=',JWTAuth::parseToken()->authenticate()->id)->first();
        $item=Item::find($request->item_id)->where('owner_id','=',$owner->id)->first();
        if($item)
        {
            $ItemsReservationRequirements->save();
    
        }
        else
        {
            return response()->json([
                'success' => false,
                'Data' => null
            ], 400);  
        }

        return response()->json([
            'success' => true,
            'data' => null
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

        $owner=Owner::where('user_id','=',JWTAuth::parseToken()->authenticate()->id)->first();
        $ItemsReservationRequirements=ItemsReservationRequirements::where('id','=',$id)
        ->WhereHas('item.owner', function ($query) use($owner)  {$query->where('id', '=',$owner->id);})
        ->first();
        if($ItemsReservationRequirements)
        {
            $ItemsReservationRequirements->delete();
            return response()->json([
                'success' => true,
                'Data' => null
            ], 200);

        }
        else
        {
            return response()->json([
                'success' => false,
                'Data' => null
            ], 400);

        }




     

        
    }
}
