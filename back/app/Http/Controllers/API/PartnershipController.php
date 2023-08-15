<?php

namespace App\Http\Controllers\API;
use Validator;
use App\Partnership;
use JWTAuth;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Http\Resources\PartnershipResource;
use App\Http\Resources\PartnershipCollection;


class PartnershipController extends Controller
{
    public function index()
    {
        $user = JWTAuth::parseToken()->authenticate();
        return response()->json([
            'success' => true ,
            'data' =>new PartnershipCollection( Partnership::where('owner_items_id','=', $user->owner->id)->get())
        ], 200);
    }

    public function store(Request $request)
    {

           $validator = Validator::make($request->all(), [
            'owner_delivery_id' => 'required',
            'status_id' =>'required'

        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'data' => $validator->messages(),
            ], 400);
        }

        $user = JWTAuth::parseToken()->authenticate();
        $partnership = Partnership::firstOrCreate(
            ['owner_items_id' => $user->owner->id,
            'owner_delivery_id' => $request->owner_delivery_id,
            'status_id' => $request->status_id]
        );
        return response()->json([
            'success' => true ,
            'data' => null
        ], 200);
    }
    public function update(Request $request)
    {
        $this->validate($request,[
            'owner_delivery_id' => 'required',
            'partnership_id' => 'required',
           ]);

           $partnership = Partnership::find($request->partnership_id);
           $partnership->owner_delivery_id = $request->owner_delivery_id;
           $partnership->save();

           return response()->json([
               'success' => true ,
               'data' => null
           ], 200);
    }

    public function changeStatus(Request $request){


      $validator = Validator::make($request->all(), [
        'partnership_id' => 'required',
        'status_id' => 'required'

   ]);

   if ($validator->fails()) {
       return response()->json([
           'success' => false,
           'data' => $validator->messages(),
       ], 400);
   }



           $partnership = Partnership::find($request->partnership_id);
           $partnership->status_id = $request->status_id;
           $partnership->save();

           return response()->json([
            'success' => true ,
            'data' => null
        ], 200);
    }

    public function PartnershipCompany()
    {
      $user = JWTAuth::parseToken()->authenticate();
      $partnership = Partnership::where('owner_delivery_id','=',$user->owner->id)->get();

      return response()->json([
       'success' => true ,
       'data' =>new PartnershipCollection($partnership)
   ], 200);

    }

    public function PartnershipCompanyByStatusId($StatusId)
    {
      $StatusId=(int)$StatusId;
      $user = JWTAuth::parseToken()->authenticate();
      $partnership = Partnership::where('owner_delivery_id','=',$user->owner->id)->where('status_id','=',$StatusId)->get();

      return response()->json([
       'success' => true ,
       'data' =>new PartnershipCollection($partnership)
   ], 200);

    }

}
