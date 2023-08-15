<?php

namespace App\Http\Controllers\API;
use Illuminate\Routing\Controller;
use Validator;
use App\ItemFeatures;
use JWTAuth;
use App\Item;
use App\Owner;

use Illuminate\Http\Request;

class ItemFeaturesController extends Controller
{
    
    public function __construct()
    {
        $this->middleware('CheckRole:owner')->only(['destroy','update','store']);
    }

    public function store(Request $request)
    {
     

        $validator = Validator::make($request->all(), [
            'item_id' => 'required|numeric',
            'feature_name' => 'required',
            'feature_price' => 'required|numeric',


            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'Data' =>  $validator->messages()
                ], 400);

                }
                $owner=Owner::where('user_id','=',JWTAuth::parseToken()->authenticate()->id)->first();
             $item=Item::where('owner_id','=',$owner->id)->where('id','=',$request->item_id)->first();



        
        $ItemFeatures = new ItemFeatures;
        $ItemFeatures->item_id = $request->item_id;
        $ItemFeatures->feature_name = $request->feature_name;
        $ItemFeatures->feature_price = $request->feature_price;

        if($item)
        {
            $ItemFeatures->save();
            return response()->json([
                'success' => true,
                'data' => null
            ], 200);
        }
        else

        {
            return response()->json([
                'success' => true,
                'data' => null
            ], 400);

        }
        

        
      
    }

    public function update(Request $request,$id)
    {
        $validator = Validator::make($request->all(), [
            'feature_name' => 'required',
            'feature_price' => 'required|numeric',


            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'Data' =>  $validator->messages()
                ], 400);

                }
                $owner=Owner::where('user_id','=',JWTAuth::parseToken()->authenticate()->id)->first();
                $ItemFeatures=ItemFeatures::where('id','=',$id)
                ->WhereHas('item.owner', function ($query) use($owner)  {$query->where('id', '=',$owner->id);})
                ->first();
        

        
       // $ItemFeatures = ItemFeatures::find($id);
        $ItemFeatures->feature_name = $request->feature_name;
        $ItemFeatures->feature_price = $request->feature_price;
        $ItemFeatures->save();

        
        return response()->json([
            'success' => true,
            'data' => null
        ], 200);
    }


    public function show(Request $request)
    {
      
    }

    public function getRateByItem($item_id)
    {
    
    }

    public function destroy($id)
    {


        $owner=Owner::where('user_id','=',JWTAuth::parseToken()->authenticate()->id)->first();
        $ItemFeatures=ItemFeatures::where('id','=',$id)
        ->WhereHas('item.owner', function ($query) use($owner)  {$query->where('id', '=',$owner->id);})
        ->first();

        if($ItemFeatures)
        {
            $ItemFeatures->delete();
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
