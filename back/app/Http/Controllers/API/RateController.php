<?php

namespace App\Http\Controllers\API;
use Illuminate\Routing\Controller;
use Validator;
use App\Rate;
use JWTAuth;
use App\Item;
use Illuminate\Http\Request;
use App\Http\Resources\RatingsOwnerCollection;
use App\Http\Resources\RatingsResource;
use App\Http\Resources\RatingsResources;


class RateController extends Controller
{
    public function store(Request $request)
    {
  

        $validator = Validator::make($request->all(), [
            'item_id' => 'required',
            'rate_value' => 'required',
            ]);


            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'Data' =>  $validator->messages()
                ], 400);

                }
        $user = JWTAuth::parseToken()->authenticate();  
        $rate = new Rate;
        $rate ->create(
            [
                'rate_value' =>$request->rate_value,
                'user_id'=>$user->id,
                'rateable_id'=>$request->item_id,
                'rateable_type'=>'App\Item',
                'comment'=>$request->comment
              
            ]);

            
        
    

        return response()->json([
            'success' => true,
            'data' => null
        ], 200);
    }


    public function show(Request $request)
    {
        $this->validate($request,[
            'item_id' => 'required'
        ]);
        $count = Rate::where('item_id','=',$request->item_id)->count();
        $rate=$count = Rate::where('item_id','=',$request->item_id)->get();
        $sum = Rate::where('item_id','=',$request->item_id)->sum('rate_value');

        return response()->json([
            'success' => true ,
            'data' => $sum/$count,
            'com'=>$rate,
        ], 200);
    }

    public function getRateByItem($item_id)
    {
        $rate = Rate::where('item_id','=',$item_id)
        ->orderBy('created_at','desc')
        ->get();

        return response()->json([
            'success' => true ,
            'data' => $rate
        ], 200);
    }


    public function GetItemsRateByOwnerId()
    {

        $user = JWTAuth::parseToken()->authenticate();
         $item = Item::where('owner_id', '=', $user->owner->id)->select('id')->get();
        foreach ($item as $i) {
            $arr[] = $i['id'];
        }
 
       $rates = Rate::whereHasMorph('rateable', [Item::class], function ($query) use($arr) {
            $query->whereIn('id',$arr);
            })
            ->orderBy('id','DESC')
            ->paginate(15)
           ;

           return   RatingsResources::collection($rates);

    }

}
