<?php

namespace App\Http\Controllers\API;

use JWTAuth;
use App\Item;
use App\Owner;
use App\AdvertisingType;
use Validator;
use App\Advertising;
use App\SubCategory;
use App\OwnerSubscriptions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controller;
use App\Http\Resources\AdvertisingsReasource;
use App\Http\Resources\AdvertisingCollection;
use App\Http\Controllers\UploadImageController;
use App\Http\Controllers\API\NotificationController;
use App\Http\Requests\AdvertisingRequest;


class AdvertisingController extends Controller
{

    public function index()
    {
    
       $owner=Owner::where('user_id','=',JWTAuth::parseToken()->authenticate()->id)->first();
        return $item =new AdvertisingCollection(Advertising::orderBy('id','DESC')->paginate(15));
 
         
       
    }

    public function getByOwnerID()
    {
        $owner=Owner::where('user_id','=',JWTAuth::parseToken()->authenticate()->id)->first();
        return $item =new AdvertisingCollection(Advertising::where('owner_id','=',$owner->id)->orderBy('id','DESC')->paginate(15));
 
         


    }


    
    public function getOwnersAdv()
    {
        $advertisings = Advertising::whereHasMorph('advertisingable', [Owner::class])
        ->WhereHas('owner.user', function ($query)  {$query->where('status_id', '=', 1);})
        ->where('is_valid','=',1)
        ->where('is_admin_approved','=',1)
        ->where('active',1)
        ->orderBy('id','DESC')
        ->paginate(15);
     return    new AdvertisingCollection($advertisings);


    }

    public function getItemsAdv()
    {


        $advertisings = Advertising::whereHasMorph('advertisingable', [Item::class])
        ->where('active',1)
        ->where('is_valid','=',1)
        ->where('is_admin_approved','=',1)
        ->with('advertisingable.image')
        ->WhereHas('owner.user', function ($query)  {$query->where('status_id', '=', 1);})
        ->orderBy('id','DESC')
        ->paginate(15);
            
        return response()->json([
            'success' => true,
            'data' => $advertisings,
        ], 200);

    }

    public function unActive($id)
    {
        $advertising = Advertising::find($id);
        $advertising->active = false;
        $advertising->save();

        return response()->json([
            'success' => true,
            'data' => null,
        ], 200);
    }

    public function show($id)
    {
        $id = (int) $id;
        $obj = Advertising::find($id)->get();

        return response()->json([
            'success' => true,
            'data' => $obj,
        ], 200);
    }


    public function OwnerSubscriptionsPackages($owner)
    {
        $OwnerSubscriptions=OwnerSubscriptions::where('owner_id','=',$owner->id)->get()->last();
        if($OwnerSubscriptions->remaining_reservations_number > 0 && $OwnerSubscriptions->status_id == 29 && $OwnerSubscriptions->status_id !== 30)
        {

       
        return true;


        }
        else

        {
           return false;
        }

    }
    public function store(Request  $request)
    {

       
        $validator = Validator::make($request->all(), [
            'item_id' => '',
            'owner_id' => '',
            'advertising_type_id' => 'required',
            'adv_description' => 'required',
            'images' => 'required|array|min:1|max:8',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'Data' => $validator->messages(),
            ], 400);
        }

        $owner=Owner::where('user_id','=',JWTAuth::parseToken()->authenticate()->id)->first();
        DB::beginTransaction();

        try {

        if($request->item_id){
            $obj = Item::where('id','=',$request->item_id)->where('owner_id','=',$owner->id)->first();
         }elseif($request->owner_id){
             $obj = Owner::find($owner->id);
         }else{
             return response()->json([
                 'success' => false,
                 'Data' => 'owner_id or item_id was missing',
             ], 400);
         }
       AdvertisingType::find($request->advertising_type_id)->days_number;

        //  if(!$this->OwnerSubscriptionsPackages($owner))
        //  {
        //      return response()->json([
        //          'success' => false,
        //          'data' => 'SubscriptionsPackages Expire',
        //      ], 400);
 
        //  }

        $advertising = $obj->advertise()->create(
            [
                'advertising_type_id' => $request->advertising_type_id,
                'adv_description' => $request->adv_description,
                'active'=>$request->active,
                'remaining_days_num'=>AdvertisingType::find($request->advertising_type_id)->days_number,
                'owner_id'=>$owner->id
            ]
            );
        


        if (count($request->images) !== 0) {
            $upload = new UploadImageController;
            $path = $upload->uploadInner($request);
            for ($i = 0; $i < count($request->images); $i++) {
                $advertising->image()->create(
                    [
                        'image_url' => $path[$i],
                    ]);
            }

        }
            DB::commit();
            return response()->json([
                'success' => true,
                'data' => null,
               
            ], 200);
           
        }
             catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'data' => $e->getMessage(),
               
            ], 400);
            }
    

    







}



       //ChangeAdvertisingStatus
        public function ChangeAdvertisingStatus()
        {

            $Advertising = Advertising::where('is_admin_approved','=',1)->get();
            foreach($Advertising as $Adver)
            {
               
                if($Adver->remaining_days_num==0)
                {
                    $Adver->is_valid=0;
                }
                $Adver->remaining_days_num-=1;
                $Adver->save();
            }

            
        }


        public function changeStatus(Request $request, $id)
    {
        $id = (int) $id;
        $validator = Validator::make($request->all(), [
            'is_admin_approved' => 'required|numeric',

        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'Data' => $validator->messages(),
            ], 400);

        }
    
         $AdvertisingCount=Advertising::whereHasMorph('advertisingable', [Owner::class])
        ->where('is_admin_approved','=',1)
        ->count();



        $advertising = Advertising::where('id','=',$id)->first();
      

        if($advertising->advertisingable_type=='App\Owner' &&  $AdvertisingCount>7)
        {
            return response()->json([
                'success' =>false,
                'data' =>'Notfound',
            ], 400);

        }
        else
        {
            if($request->is_admin_approved==1)
            {
                $notification=new NotificationController();
                $notifications= $advertising->Notification()->create(
                [
                    'user_id' =>$advertising->owner->user->id,
                    'notification_type_id' =>5,
                    'notification_title'=>'تم تفعيل اعلانك في منصه إحجز إلي',
                    'status_id'=>26,
                    'notification_body'=>'تم تفعيل اعلانك في منصه إحجز إلي'  
                ]);
                if($notifications)
                {
                
                    $send_data=['user'=>$advertising->owner->user,'notification_type_id'=>$notifications->notification_type_id,'notification_title'=>$notifications->notification_title,'notification_body'=>$notifications->notification_body];
                    $notification->sendPhoneNotification($send_data);
                }


            }
            

            $advertising->is_admin_approved = $request->is_admin_approved;
            $advertising->remaining_days_num=$advertising->advertisingType->days_number;
            $advertising->save();
            return response()->json([
                'success' => true,
                'data' => null,
            ], 200);

        }


      
        
    }


    public function update(Request $request, $id)
    {
        $id = (int) $id;
        $validator = Validator::make($request->all(), [
            'advertising_type_id' => 'required',
            'adv_description' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'Data' => $validator->messages(),
            ], 400);

        }
        $owner=Owner::where('user_id','=',JWTAuth::parseToken()->authenticate()->id)->first();
        $advertising = Advertising::where('id','=',$id)->where('owner_id','=',$owner->id)->first();
        $advertising->advertising_type_id = $request->advertising_type_id;
        $advertising->adv_description = $request->adv_description;
        $advertising->active = $request->active;
        $advertising->save();

        if (count($request->images) !== 0) {
            $upload = new UploadImageController;
            $path = $upload->uploadInner($request);
            for ($i = 0; $i < count($request->images); $i++) {
                $advertising->image()->create(
                    [
                        'image_url' => $path[$i],
                    ]);
            }

        }

        return response()->json([
            'success' => true,
            'data' => null,
        ], 200);
    }

    public function destroy($id)
    {

        $id = (int) $id;
        $owner=Owner::where('user_id','=',JWTAuth::parseToken()->authenticate()->id)->first();
        $advertising = Advertising::where('id','=',$id)->where('owner_id','=',$owner->id)->first();
if($advertising)
{
  $advertising->delete();
  return response()->json([
    'success' => true,
    'data' => null,
], 200);
}
else
{
    return response()->json([
        'success' =>false,
        'data' => null,
    ], 400);
}
      
       
    }
}
