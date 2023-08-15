<?php

namespace App\Http\Controllers\Api;
use App\Owner;
use App\Address;
use Validator;
use App\OwnerCategory;
use JWTAuth;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Http\Controllers\UploadImage;
use App\Http\Controllers\UploadImageController;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use App\Http\Resources\AddressCollection;
use App\Http\Resources\AddressResource;
use App\Http\Resources\OwnerCollection;
use App\Http\Resources\OwnerResource;
class OwnerController extends Controller
{
    protected $smsUrl = 'https://sms-gw.net:91/v2/api/Gateway/SendMessage';
    protected $smsToken = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJodHRwOi8vc2NoZW1hcy54bWxzb2FwLm9yZy93cy8yMDA1LzA1L2lkZW50aXR5L2NsYWltcy9uYW1laWRlbnRpZmllciI6IjE2ZGMwNmEyLThkYWQtNDcyYy1hNDBhLWY3YmQwYWVhZDAxNyIsImVtYWlsIjoiZW5qYXpAc21zLWd3Lm5ldCIsImdpdmVuX25hbWUiOiJFTkpBWiIsImh0dHA6Ly9zY2hlbWFzLnhtbHNvYXAub3JnL3dzLzIwMDUvMDUvaWRlbnRpdHkvY2xhaW1zL25hbWUiOiJFTkpBWiIsImh0dHA6Ly9zY2hlbWFzLm1pY3Jvc29mdC5jb20vd3MvMjAwOC8wNi9pZGVudGl0eS9jbGFpbXMvcm9sZSI6IkN1c3RvbWVyIiwiaHR0cDovL3NjaGVtYXMueG1sc29hcC5vcmcvd3MvMjAwOS8wOS9pZGVudGl0eS9jbGFpbXMvYWN0b3IiOiIyIiwianRpIjoiNjNmNWFlOGEtODM1OS00M2EyLTkwNjgtMTVjM2Y5OTc5NjA1IiwibmJmIjoxNTgwMTY0Mjk2LCJleHAiOjE2MTE3ODY2OTYsImlzcyI6Iklzc3VlciIsImF1ZCI6IkF1ZGllbmNlIn0.yukJbD_zVIoE-gG2HKMxI8EpBeaohNwIxG_vnuS2TAw';
    protected $data = [];
    protected $code;
    protected $smsData = [];


    public function index()
    {
        $all = Owner::with('user','image','ownerCategory')
        ->orderBy('id', 'desc')
        ->paginate(15);
        return response()->json([
            'success' => true,
            'data' => $all
        ], 200);
    }



    


    public function Search(request $request)
    {

        

        $owners = QueryBuilder::for(Owner::class)
        ->WhereHas('user', function ($query)  {
            $query->where('role_id', '=',2);
        })
        ->allowedSorts('id')
        ->allowedFilters(['id','user.status.id','owner_phone','ownerCategory.category_id'])
        ->paginate(15);

       return new OwnerCollection($owners);
       

    }


    public function getOwnersByStatusId($status_id){
       $status_id=(int)$status_id;
        $all = Owner::with('user','image','ownerCategory')

        ->WhereHas('user', function ($query) use ($status_id) {
            $query->where('status_id', '=', $status_id);
        })
        // ->WhereHas('item.bill', function ($query) {
        //     $query->where('status_id', '=', 31);
        // })

        ->orderBy('id', 'desc')
        ->paginate(15);
        return response()->json([
            'success' => true,
            'data' => $all
        ], 200);
    }




    public function getOwnersByBillsStatusId($status){
        $status_id=(int)$status;
         $all = Owner::with('user','image','ownerCategory')
 
         ->WhereHas('user', function ($query) {
             $query->where('status_id', '=', 1);
         })
         ->WhereHas('item.bill', function ($query) use($status_id) {
             $query->where('status_id', '=',$status_id);
         })
 
         ->orderBy('id', 'desc')
         ->paginate(15);
         return response()->json([
             'success' => true,
             'data' => $all
         ], 200);
     }
 


    public function sms()
    {
        $response = Http::withToken($this->smsToken)
            ->post($this->smsUrl, $this->smsData);
        return $response;
    }
    public function getAllOwnersDelivery(){
        $deliveries = Owner::with('user')->where('owner_type_id',2)
        ->orderBy('id', 'desc')
        ->paginate(15);
        return response()->json([
            'success' => true,
            'data' => $deliveries
        ], 200);
    }

    public function getOwnersDelivery(){
        $deliveries = Owner::Delveries('Active')
        ->orderBy('id', 'desc')
        ->paginate(15);
        return response()->json([
            'success' => true,
            'data' => $deliveries
        ], 200);
    }

    public function getAllOwnersItems(){
        $deliveries = Owner::Items('Active')
        ->orderBy('id', 'desc')
        ->paginate(15);
        return response()->json([
            'success' => true,
            'data' => $deliveries
        ], 200);
    }

    public function store(Request $request)
    {
        $this->validate($request,[
            'owner_type_id' => 'require|numeric',
            'owner_brand_name' => 'required',
            'owner_email' => 'email|unique:owners',
            'owner_phone' => 'required|unique:owners',
            'owner_image' => 'required|array|min:1',
            'status_id' => 'require|numeric',
            'category_id' => 'required|numeric'
        ]);

        $user = JWTAuth::parseToken()->authenticate();
        try{
            $owner = new Owner;
            $owner->owner_type_id = $request->owner_type_id;
            $owner->user_id = $user->id;
            $owner->owner_brand_name = $request->owner_brand_name;
            $owner->owner_email = $request->owner_email;
            $owner->owner_phone = $request->owner_phone;
            $owner->status_id = 1;
            $owner->save();
            if(count($request->owner_image)!==0)
            {
            $upload = new UploadImageController;
            $path = $upload->uploadInner($request);
            $owner->image()->create(
                [
                    'image_url' =>$path[$i],
                ]);
            }

            $ownerCategory = new OwnerCategory;
            $ownerCategory->owner_id = $owner->id;
            $ownerCategory->category_id = $request->category_id;
            $ownerCategory->status_id = 1;
            $ownerCategory->save();
        }catch(\Illuminate\Database\Eloquent $e){
            return response()->json([
                'success' => false,
                'data' => null
            ], 400);
        }

        return response()->json([
            'success' => true,
            'data' => null
        ], 200);


    }

    public function changeStatusOwner(Request $request){



        $validator = Validator::make($request->all(), [
            'owner_id' => 'required|numeric',
            'status_id' => 'required|numeric',
            ]);


            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'Data' =>  $validator->messages()
                ], 400);

                }
        $owner = Owner::find($request->owner_id);
        $user=$owner->user;

       if($request->status_id==1 || $request->status_id==2 || $request->status_id==24)
       {
        //    if($request->status_id==1)
        //    {
        //     $this->smsData['number'] =$user->user_phone;
        //     $this->smsData['text'] = 'https://ahjez-ely.com/owner تم تفعيل حسابك في منصة إحجز إلي للدخول للمنصه اضفط على الرابط التالي';
        //     $this->smsData['messageType'] = 3;
        //     $this->smsData['sentThrough'] = 1;
        //     $this->sms();
        //    }
        $user->status_id = $request->status_id;
        $user->save();
        return response()->json([
            'success' => true,
            'data' => null
        ], 200);

       }
       else
       {
        return response()->json([
            'success' => false,
            'data' => null
        ], 400);


       }






    }

    public function addAddress(Request $request)
    {
        $owner=Owner::where('user_id','=',JWTAuth::parseToken()->authenticate()->id)->first();

        $validator = Validator::make($request->all(), [
          
            'province_id' => 'required|numeric',
            // 'lat' => 'required|numeric',
            // 'long' => 'required|numeric',
            'address_descraption'=>'required'

            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'Data' =>  $validator->messages()
                ], 400);

                }
                
                if($request->long==null || $request->lat==null  || !isset($request->lat)  || !isset($request->long))
                {


                 //   echo 'asdsa';
                     $ip ='37.237.128.5';
                    $access_key = '404b133e44aa0ced6faaf521cb09368c';
                    $ch = curl_init('http://api.ipstack.com/'.$ip.'?access_key='.$access_key.'');
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    $json = curl_exec($ch);
                    curl_close($ch);
                    $api_result = json_decode($json, true);
                    $long=$api_result['longitude'];
                    $lat=$api_result['latitude'];
                    
            
                }
                else
                {
                    $long=$request->long;
                    $lat=$request->lat;
        
        
                }

                $owner->address()->create(
                    [
                        'province_id' =>$request->province_id,
                        'long' =>$long,
                        'lat' =>$lat,
                        'address_descraption' =>$request->address_descraption,
                    ]);
     
        
        return response()->json([
            'success' => true,
            'data' =>new AddressCollection($owner->address),
        ], 200);
        
    }


    public function updateAddress(Request $request,$id)
    {
        $owner=Owner::where('user_id','=',JWTAuth::parseToken()->authenticate()->id)->first();

        $validator = Validator::make($request->all(), [
          
            'province_id' => 'required|numeric',
            //'lat' => 'required|numeric',
           // 'long' => 'required|numeric',
            'address_descraption'=>'required'

            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'Data' =>  $validator->messages()
                ], 400);

                }


                $address=Address::find($id);
                $address->province_id=$request->province_id;
           

                if($request->long==null || $request->lat==null  || !isset($request->lat)  || !isset($request->long))
            {
             

                    
                     $ip =$_SERVER['REMOTE_ADDR'];

           
                    $access_key = '404b133e44aa0ced6faaf521cb09368c';
                    $ch = curl_init('http://api.ipstack.com/'.$ip.'?access_key='.$access_key.'');
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    $json = curl_exec($ch);
                    curl_close($ch);
                    $api_result = json_decode($json, true);
                    $address->long=$api_result['longitude'];
                    $address->lat=$api_result['latitude'];
                    
            
                }
                else
                {
                    $address->long=$request->long;
                    $address->lat=$request->lat;
        
        
                }
                $address->address_descraption=$request->address_descraption;
                $address->save();
              
     
        
        return response()->json([
            'success' => true,
            'data' =>new AddressCollection($owner->address),
        ], 200);
        
    }


    

    public function getAddress()
        {

           $owner=Owner::with(['address'])
            ->where('user_id','=',JWTAuth::parseToken()->authenticate()->id)
            ->first();

            
            return response()->json([
                'success' => true,
                'data' =>new AddressCollection($owner->address),
            ], 200);
            
        }
        
        
    public function show(Request $request)
    {
        $user=JWTAuth::parseToken()->authenticate();

        if($user)
        {
            
            return new OwnerResource($user->owner);

        }
        
        else {
            return response()->json([
                'success' => false,
                'data' => 'Not Found',
            ], 400);
        }
      
    }


    public function update(Request $request)
    {


        $user=JWTAuth::parseToken()->authenticate();



        $validator = Validator::make($request->all(),[
            'owner_barnd_name' => 'required',
         //   'owner_email' =>'email|unique:users,user_email,'.$user->id,
           // 'images' => 'array|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'data' => $validator->messages(),
            ], 400);
        }


        try{
            
            $user->full_name=$request->full_name;
            $user->user_email=$request->owner_email;

            $owner = $user->owner;
            $owner->owner_barnd_name = $request->owner_barnd_name;
            $owner->owner_email = $request->owner_email;
            
            $owner->save();
            $user->save();

            if($request->images)
            {
                if(count($request->images)!==0)
                {
                    
                $upload = new UploadImageController;
                $path = $upload->uploadInner($request);
                for($i = 0;$i < count($request->images);$i++){
                    $user->image()->create(['image_url' =>$path[$i],]);
                        
                            
                        
                }


                }

            }

            
        }catch(\Illuminate\Database\Eloquent $e){
            return response()->json([
                'success' => false,
                'data' => null
            ], 400);
        }

        return response()->json([
            'success' => true,
            'data' => null
        ], 200);
    }


    public function destroy(Request $request)
    {
        Owner::find($request->owner_id)->delete();
        return response()->json([
            'success' => true,
            'data' => null
        ], 200);
    }

}
