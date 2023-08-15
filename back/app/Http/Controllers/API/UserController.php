<?php
use App\Http\Controllers\API\NotificationController;
namespace App\Http\Controllers\API;
use App\Http\Controllers\API\UploadController;
use App\Http\Controllers\UploadImageController;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Http\Resources\UserCollection;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
require_once 'Twilio/autoload.php';
use Twilio\Rest\Client;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\UserResource;
use App\SubscriptionPackages;
use App\OwnerSubscriptions;
use Khalidsheet\Oursms\Oursms;
use App\OwnerCategory;
use App\Reservation;
use Validator;
use App\Address;
use App\Otp;
use App\Category;
use App\Owner;
use App\Image;
use App\DevicesTokens;
use App\Item;
use App\User;
use Exception;
use JWTAuth;

class UserController extends Controller
{


   
    protected $data = [];
    protected $code;
    protected $smsData = [];
    private $user;
    private  $oursmsFields = ['userId' =>1, 'key' => "123E6BCA5555D0DADF39A3D2958ECCB0"];



    public function __construct()
    {
    
        $this->data = ['success' => false, 'data' => null];
        $this->code = 400;


       
    }


   

    public function signUpClient(request $request)
    {

        $data_user = json_decode(User::where('user_phone','=',$request->user_phone)->where('role_id','=',1)->first(), true);

        if(User::where('user_phone','=',$request->user_phone)->where('register_by_owner','=',1)->first())
        {
    
            $user=User::where('user_phone','=',$request->user_phone)->where('register_by_owner','=',1)->first();
            $user->register_by_owner=0;
            $phone_vali='required|min:13|numeric';
          

        }
        else if(User::where('user_phone','=',$request->user_phone)->where('role_id','=',2)->first() && empty($data_user))
        {
            $user = new User;
            $phone_vali='required|min:13|numeric';

        }
        else
        {

            $phone_vali='required|unique:users|min:13|numeric';
            $user = new User;
        }



        $validator = Validator::make($request->all(), [
            'full_name' => 'required',
            'password' => 'required|min:8|confirmed',
            'user_phone' =>$phone_vali,
            'images' => 'array',
        ]);


       
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'data' => $validator->messages(),
            ], 400);
        }
        
        $user->full_name = $request->full_name;
        $user->password = app('hash')->make($request->password);
        $user->user_phone = $request->user_phone;
        $user->role_id =1;
        $user->status_id =2;

        $user->save();

        if($request->mobile_token)
        {
            $DevicesTokens=DevicesTokens::
                 where('user_id','=',$user->id)
                ->where('device_uuid','=',$request->device_uuid)
                ->where('role_id','=',$user->role_id)
                ->first();

            if(empty($DevicesTokens)){
            $DevicesTokenss=new DevicesTokens;
            $DevicesTokenss->user_id=$user->id;
            $DevicesTokenss->device_token=$request->mobile_token;
            $DevicesTokenss->device_uuid=$request->device_uuid;
            $DevicesTokenss->save();
            }
            else
            {
                $DevicesTokens->device_token=$request->mobile_token;
                $DevicesTokens->save();
            }
        }


        
         
            $ip =$_SERVER['REMOTE_ADDR'];
            $access_key = '404b133e44aa0ced6faaf521cb09368c';
            $ch = curl_init('http://api.ipstack.com/'.$ip.'?access_key='.$access_key.'');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $json = curl_exec($ch);
            curl_close($ch);
            $api_result = json_decode($json, true);
            $long=$api_result['longitude'];
            $lat=$api_result['latitude'];
            
    
       
        
        
           
            $user->address()->create(
                [
                    'long' => $long,
                    'lat' => $lat,
                ]);

        

               
        try {
            DB::beginTransaction();
            $user->save();

            //Otp
           $this->verifyNumber($user);
    
          //  print($message->sid);
            if (count($request->images) !== 0) {
                $upload = new UploadImageController;
                $path = $upload->uploadInner($request);
                $user->image()->create(
                    [
                        'image_url' => $path[0],
                    ]);
            }

         
        } catch (\Illuminate\Database\QueryException $e) {
            dd($e);
            DB::rollback();
            return response()->json([
                'success' => false,
                'data' => 'data did not inserted',
            ], 400);
        }
        DB::commit();
        $request->request->add(['role_id' => 1]);
        $credentials = $request->only(['user_phone', 'password','role_id']);
        $token = JWTAuth::attempt($credentials);
       // $this->sms();

        return response()->json([
            'success' => true,
            'token' => $token,
        ], 200);

    }


    public function getAllOwnersUsers()
    {
         $users=User::with('status')->where('role_id','=',1)->orWhere('role_id','=',2)
         -> orderBy('id', 'desc')
         ->paginate(15);
         return response()->json([
            'success' => true,
            'data' =>$users
        ], 200);
        // return  new Usercollection($users);

    }


    public function DeletedUser($id)
    {
       
      
        $TimeToOpen=User::
       // where('role_id','=',1)->orWhere('role_id','=',2)->
        where('id','=',$id)->delete();
     //   $TimeToOpen->delete();

        return response()->json([
            'success' => true,
            'Data' => null
        ], 200);
    }

    public function signUpOthers(request $request)
    {
        
        $data_user_owner = json_decode(User::where('user_phone','=',$request->user_phone)->where('role_id','=',2)->first(), true);
        $data_user_user = json_decode(User::where('user_phone','=',$request->user_phone)->where('role_id','=',1)->first(), true);

       if(!empty($data_user_user) && empty($data_user_owner))
        {
             $phone_vali='required|min:13|numeric';
             $user = new User;
        }
        else
        {

            $phone_vali='required|unique:users|min:13|numeric';
            $user = new User;
        }

        $validator = Validator::make($request->all(), [
            'full_name' => 'required',
            'password' => 'required|confirmed|min:8',
            'user_phone' => $phone_vali,
            'owner_brand_name' => 'required',
            'category_id' => 'required',
            'owner_phone' => '',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'data' => $validator->messages(),
            ], 400);
        }
      
        $user->full_name = $request->full_name;
        $user->password = app('hash')->make($request->password);
        $user->user_phone = $request->user_phone;
        $user->role_id = 2;
        $user->status_id = 24;

        
        if($request->mobile_token)
        {
            $DevicesTokens=DevicesTokens::
                 where('user_id','=',$user->id)
                ->where('device_uuid','=',$request->device_uuid)
                ->where('role_id','=',$user->role_id)
                ->first();
            if(empty($DevicesTokens)){
            $DevicesTokenss=new DevicesTokens;
            $DevicesTokenss->user_id=$user->id;
            $DevicesTokenss->device_token=$request->mobile_token;
            $DevicesTokenss->device_uuid=$request->device_uuid;
            $DevicesTokenss->save();
            }
            else
            {
                $DevicesTokens->device_token=$request->mobile_token;
                $DevicesTokens->save();
            }
        }


      
     

     
        
   

        try {

            $user->save();

            //Otp
          //  $this->verifyNumber($user);
          Otp::create(['user_id' => $user->id, 'verify_number' => '111111','ip_address'=>'']);
    
            $owner = new Owner;
            $owner->owner_type_id =1;
            $owner->user_id = $user->id;
            $owner->owner_barnd_name = $request->owner_brand_name;
            $owner->owner_phone = $request->owner_phone;
            $owner->status_id = 1;
            $owner->save();
            $ownerCategory = new OwnerCategory;
            $ownerCategory->owner_id = $owner->id;
            $ownerCategory->category_id = $request->category_id;
            $ownerCategory->status_id = 1;
            $ownerCategory->save();
            

           
        } catch (\Illuminate\Database\QueryException $e) {
      
            dd($e);
            DB::rollback();
            return response()->json([
                'success' => false,
                'data' => 'data did not inserted',
            ], 400);
        }
        DB::commit();
        $request->request->add(['role_id' =>2]);
        $credentials = $request->only(['user_phone', 'password','role_id']);

   
        $token = JWTAuth::attempt($credentials);
        
        if (!empty($request->file('file'))) {
            UploadController::store($user);
        }

        return response()->json([
            'success' => true,
            'token' => $token,
            'id'=>$user->id
        ], 200);

    }


    
    
    public function sms()
    {

        
        $response = Http::withToken($this->smsToken)
            ->post($this->smsUrl, $this->smsData);
        return $response;
    }


    public function verifyNumber($user)
    {
            $sms_content = mt_rand(100000, 999999);
         

            $x=Otp::where('ip_address','=',$_SERVER['REMOTE_ADDR'])->latest()->take(1)->get()
           ;
           //->take(1)->get();
            if(count( Otp::where('ip_address','=',$_SERVER['REMOTE_ADDR'])->latest()->take(100)->get() )>5  && $x[0]->user->user_phone)
            {
                return false;
            }

            if(count( Otp::where('ip_address','=',$_SERVER['REMOTE_ADDR'])->latest()->take(100)->get() )>5 )
            {
                return false;
            }
            Otp::create(['user_id' => $user->id, 'verify_number' => $sms_content,'ip_address'=>$_SERVER['REMOTE_ADDR']]);


            $sid    = "ACbc7e12ab3a235e9c7a4899564686f12f"; 
            $token  = "bb8d3512b19231cdd72d89f36c841b01"; 
            $twilio = new Client($sid, $token); 
            $message = $twilio->messages
            ->create('+'.$user->user_phone, // to
                     [
                         "body" => " رمز التحقق الخاص بك هو ".$sms_content,
                         "messagingServiceSid" => "MGe6a03e6cb5aaedb89cc5a9a8c49b90d8",
                     ]
            );

    }
    public function accountActivateByOpertion(request $request,$otpNumber)
    {


        $validator = Validator::make($request->all(), [
            'user_id' => 'numeric|required',
          
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'data' => $validator->messages(),
            ], 400);
        }

        $user=user::find($request->user_id);
        if ($user) {

            $otpObject = Otp::where('user_id','=',$user->id)
                        ->where('verify_number','=',$otpNumber)
                        ->get();

        } else {
            return response()->json([
                'success' => false,
                'data' => 'Users Not Found',
            ], 400);
        }

      
        if (count($otpObject)==0) {
            return response()->json([
                'success' => false,
                'data' => 'otp invalid',
            ], 400);
        } else {
            $end_date = strtotime(date("Y-m-d H:i:s"));
            $start_date = strtotime($otpObject[0]->created_at);
            $diffInHoures = floor(($end_date - $start_date) / 60 / 60);
            if ($diffInHoures >= 25) {
                return response()->json([
                    'success' => false,
                    'data' => 'otp expired',
                ], 400);
            }
        }
        $user = User::find($user->id);
        if($user->role_id==1)
        {
            $user->status_id = 1;

        }
        elseif($user->role_id==2)
        {
            $user->status_id = 24;

        }
        
        $user->save();

        return response()->json([
            'success' => true,
            'data' => 'user activated',
        ], 200);
    }



    public function accountActivate(request $request,$otpNumber)
    {
      
        $user=JWTAuth::parseToken()->authenticate();
        if ($user) {

            if($user->role_id==2)
            {
           ///     echo 'herer';
                $otpObject = Otp::where('user_id','=',$user->id)
              //  ->where('verify_number','==',111111)
                  ->get();

            }
            else
            {
                $otpObject = Otp::where('user_id','=',$user->id)
                ->where('verify_number','=',$otpNumber)
                  ->get();

            }
           

        } else {
            return response()->json([
                'success' => false,
                'data' => 'Token Invalid',
            ], 400);
        }

      
        if (count($otpObject)==0) {
            return response()->json([
                'success' => false,
                'data' => 'otp invalid',
            ], 400);
        } else {
            $end_date = strtotime(date("Y-m-d H:i:s"));
            $start_date = strtotime($otpObject[0]->created_at);
            $diffInHoures = floor(($end_date - $start_date) / 60 / 60);
            if ($diffInHoures >= 25) {
                return response()->json([
                    'success' => false,
                    'data' => 'otp expired',
                ], 400);
            }
        }
        $user = User::find($user->id);
        if($user->role_id==1)
        {
            $user->status_id = 1;
            $user->save();
            return response()->json([
                'success' => true,
                'data' => 'user activated',
            ], 200);

        }
        elseif($user->role_id==2)
        {
           
           $token = JWTAuth::getToken();
           $token= JWTAuth::refresh($token);
           $this->SetOwnerSubscriptions($user->owner);
            if($user->role->role_name=="owner")
        {
           $item_num=$user->owner->item->count();
        }
        else
        {
            $item_num='';

        }
            $data = [
                
                    'id' => $user->id,
                    'token' => $token,
                    'id' => $user->id,
                    'user_name' => $user->full_name,
                    'user_phone' => $user->user_phone,
                    'user_image' => ($user->image) ? $user->image->image_url : null,
                    'user_email' => $user->user_email,
                    'user_role' => $user->role->role_name,
                    'user_status' => $user->status->status_name,
                    'item_num'=>$item_num
                
            ];
         //   $user->status_id =24;
         $user->status_id =1;
            $user->save();
            return response()->json([
                'success' => true,
                'data' =>$data,
            ], 200);

        }
        
       

        
    }

public function UpdatePassword(request $request)
{
    $validator = Validator::make($request->all(), [
        'old_password'=>'required|min:8',
        'password' => 'required|min:8|confirmed',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'data' => $validator->messages(),
        ], 400);
    }

    $user = JWTAuth::parseToken()->authenticate();
    $user = User::find($user->id);

    if (Hash::check($request->old_password, $user->password)) { 

        $user->password = app('hash')->make($request->password);
        $user->save();
        
        // $user->fill([
        //  'password' => Hash::make($request->password)
        //  ])->save();
     
         return response()->json([
            'success' => true,
            'data' =>null,
        ], 200);
     
     } else {
        return response()->json([
            'success' => false,
            'data' => 'old password false',
        ], 400);
     }
    
}


    public function UpdateOwnerByOpertions(request $request)
    {
        $user = User::find($request->user_id);

        
        $validator = Validator::make($request->all(), [
            'full_name' => 'required',
            'user_id' => 'required|numeric',
            'owner_brand_name' => 'required',
            'category_id' => 'required',
            'owner_phone' => '',
            'status_id'=>'required|numeric'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'data' => $validator->messages(),
            ], 400);
        }
        
        if($request->status_id==1  &&  $user->status_id==24)
        {
          
         $this->smsData['number'] =$user->user_phone;
         $this->smsData['text'] = ' شكراً '. $user->full_name.' تم تفعيل حسابك في منصة إحجز إلي  سيتم نشر حجوزاتك في المنصه';
         $this->smsData['messageType'] = 3;
         $this->smsData['sentThrough'] = 1;
      //   $this->sms();


         $notification=new NotificationController();
         $notifications=$user->Notification()->create(
            [
                'user_id' =>$user->id,
                'notification_type_id' =>4,
                'notification_title'=>'تم تفعيل الحساب في منصه إحجز إلي',
                'status_id'=>26,
                'notification_body'=>'تم تفعيل الحساب في منصه إحجز إلي' 
            ]);
            if($notifications)
            {
                $send_data=['user'=>$user,'notification_type_id'=>$notifications->notification_type_id,'notification_title'=>'تم تفعيل الحساب في منصه إحجز إلي','notification_body'=>'تم تفعيل الحساب في منصه إحجز إلي'];
                $notification->sendPhoneNotification($send_data);
            }

        }
        
        $owner=$user->owner;
        $user->full_name = $request->full_name;
        $user->status_id=$request->status_id;    
        $owner->owner_barnd_name= $request->owner_brand_name;
        $owner->owner_email= $request->user_email;
        $ownerCategory=$owner->ownerCategory;
        $ownerCategory->category_id=$request->category_id;
        if(OwnerSubscriptions::where('owner_id','=',$owner->id)->get()->last()==null || $request->subscriptions_id !== OwnerSubscriptions::where('owner_id','=',$owner->id)->get()->last()->subscription_package_id)
      
        {
            $this->SetOwnerSubscriptions($owner,$request->subscriptions_id);
          }

        try {
            DB::beginTransaction();
            $user->save();
            $owner->save();
         
          
            $ownerCategory->save();

     
           
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'data' => 'data did not updated',
            ], 400);
        }
        DB::commit();
        if (!empty($request->file('file'))) {
            UploadController::store($user);
        }
        return response()->json([
            'success' => true,
            'data' => null,
        ], 200);
    }


    public function getAdminDashbourdCounts()
    {
    
      
        $owner_role_id=2;
        $count = [
            'AllOwner'=>User::where('role_id','=',2)->count(),
            'AllUser'=>User::where('role_id','=',1)->count(),
           'OwnerUnactive' => User::GetStatusCount('unactive',$owner_role_id),
           'OwnerActive' => User::GetStatusCount('active',$owner_role_id),
           'Ownerbloked' => User::GetStatusCount('bloked',$owner_role_id),
           'ReserverationsCount' => Reservation::count(),
           'ItemsCount' => Item::count(),
           'CategoryCount' => Category::count(),
          
           
           
        ];
        return response()->json([
            'success' => true,
            'data' => $count,
        ], 200);


    }



    public function SetOwnerSubscriptions($owner)
    {
        
        
        $subscriptions_id=1;
      //echo $owner->id;
        // if(count(OwnerSubscriptions::where('owner_id','=',$owner->id)->get())==0)
        // {
            $subscription = new OwnerSubscriptions;
            $subscription->owner_id =$owner->id;
            $subscription->subscription_package_id =$subscriptions_id;
            $subscription->status_id=29;
            $subscription->is_free=1;
            $subscription->remaining_reservations_number=SubscriptionPackages::find($subscriptions_id)->reservations_count;
            $subscription->save();

        // }
        // else
        // {
            

        // }
       


    }
    public function update(request $request)
    {
        
        $user = JWTAuth::parseToken()->authenticate();
       $user = User::find($user->id);

        $validator = Validator::make($request->all(), [
    
            'full_name' => 'required',
            'user_email' =>  'email|unique:users,user_email,'.$user->id,
            //'password' => 'required',
           // 'user_phone' => 'required',
            'province_id' => 'numeric',
            'images' => '',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'data' => $validator->messages(),
            ], 400);
        }
        try {
            DB::beginTransaction();
        $user->full_name = $request->full_name;
        $user->user_email = $request->user_email;
        $user->password = app('hash')->make($request->password);
        $user->user_phone = $request->user_phone;
        $user->save();
        
        if($request->long==null || $request->long==null)
        {
         
            $ip =$_SERVER['REMOTE_ADDR'];
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
            $long= $request->long;
            $lat= $request->lat;


        }

        
           
            $user->address()->update(
                [
                    'province_id' => $request->province_id,
                    'long' => $long,
                    'lat' => $lat,
                    'address_descraption' => $request->address_descraption,
                ]);
                
                return  $user->address;
            if (count($request->images) !== 0) {
                $upload = new UploadImageController;
                $path = $upload->uploadInner($request);
                $user->image()->update(
                    [
                        'image_url' => $path,
                    ]);
            }


        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'data' => 'data did not updated',
            ], 400);
        }
        DB::commit();
        if (!empty($request->file('file'))) {
            UploadController::store($user);
        }
        return response()->json([
            'success' => true,
            'data' => null,
        ], 200);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_phone' => 'required',
            'password' => 'required|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'data' => $validator->messages(),
            ], 400);
        }

        $request->request->add(['role_id' => 1]);
        $credentials = $request->only(['user_phone', 'password','role_id']);
       //$credentials = $request->only(['user_phone', 'password']);
        
        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                throw new Exception('invalid_credentials');
            }
            
            $user = auth()->user();
            if($user->status_id !==1 )
            {
                if($user->status_id==2)
                {
                    
                    //Otp
                    $this->verifyNumber($user);

                    return response()->json([
                        'success' => false,
                        'data' => 'User Unactive',
                        'token'=>$token
                    ],200);
                }
            }

        if($user->role->role_name=="owner")
        {
           $item_num=$user->owner->item->count();
        }
        else
        {
            $item_num='';

        }

            $this->data = [
                'sucess' => true,
                'data' => [
                    'id' => $user->id,
                    'token' => $token,
                    'id' => $user->id,
                    'user_name' => $user->full_name,
                    'user_phone' => $user->user_phone,
                    'user_image' => ($user->image) ? $user->image->image_url : null,
                    'user_email' => $user->user_email,
                    'user_role' => $user->role->role_name,
                    'user_status' => $user->status->status_name,
                    'item_num'=>$item_num
                ],
            ];
            $this->code = 200;


            if($request->mobile_token)
            {
                $DevicesTokens=DevicesTokens::
                 where('user_id','=',$user->id)
                ->where('device_uuid','=',$request->device_uuid)
                ->where('role_id','=',$user->role_id)
                ->first();
                if(empty($DevicesTokens)){
                $DevicesTokenss=new DevicesTokens;

                $DevicesTokenss->user_id=$user->id;
                $DevicesTokenss->device_token=$request->mobile_token;
                $DevicesTokenss->device_uuid=$request->device_uuid;
                $DevicesTokenss->role_id=1;
                $DevicesTokenss->save();


                }
                else
                {
                    $DevicesTokens->device_token=$request->mobile_token;
                    $DevicesTokens->save();
                }
            }

        } catch (Exception $e) {
            $this->data['data'] = $e->getMessage();
            $this->data['code'] = 401;
        } catch (JWTException $e) {
            $this->data['data'] = 'Could not create token';
            $this->data['code'] = 500;
        }
        return response()->json($this->data, $this->code);
    }





  public function loginOwnerSmart(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_phone' => 'required',
            'password' => 'required|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'data' => $validator->messages(),
            ], 400);
        }

        $request->request->add(['role_id' => 2]);
        $credentials = $request->only(['user_phone', 'password','role_id']);
       //$credentials = $request->only(['user_phone', 'password']);
        
        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                throw new Exception('invalid_credentials');
            }
            
           $user = auth()->user();
            if($user->status_id!==1)
            {
                if($user->status_id==2)
                {
                    $sms_content = mt_rand(100000, 999999);

                    Otp::create(['user_id' => $user->id, 'verify_number' => $sms_content]);

                  
                    $sms_content = mt_rand(100000, 999999);
                    $curl = curl_init();
                    curl_setopt_array($curl, array(
                      CURLOPT_URL => 'https://oursms.app/api/v1/SMS/Add/SendOtpSms',
                      CURLOPT_RETURNTRANSFER => true,
                      CURLOPT_ENCODING => '',
                      CURLOPT_MAXREDIRS => 10,
                      CURLOPT_TIMEOUT => 0,
                      CURLOPT_FOLLOWLOCATION => true,
                      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                      CURLOPT_CUSTOMREQUEST => 'POST',
                      CURLOPT_POSTFIELDS =>'{
                        "userId":1,
                        "key": "123E6BCA5555D0DADF39A3D2958ECCB0",
                        "phoneNumber":"'.$request->user_phone.'",
                        "otp":"'.$sms_content.'"
                    }',
                      CURLOPT_HTTPHEADER => array(
                        'Content-Type: application/json',
                        'Cookie: ARRAffinity=d8317cc4cf9f66cc009ee7be5032f942a49d32e34d351a5e76312d2dabbe2c21; ARRAffinitySameSite=d8317cc4cf9f66cc009ee7be5032f942a49d32e34d351a5e76312d2dabbe2c21'
                      ),
                    ));
            
                    
                    $response = curl_exec($curl);
                    curl_close($curl);


                    return response()->json([
                        'success' => false,
                        'data' => 'User Unactive',
                        'token'=>$token
                    ],200);
                }
            }

        if($user->role->role_name=="owner")
        {
           $item_num=$user->owner->item->count();
        }
        else
        {
            $item_num='';

        }

            $this->data = [
                'success' => true,
                'data' => [
                    'id' => $user->id,
                    'token' => $token,
                    'id' => $user->id,
                    'user_name' => $user->full_name,
                    'user_phone' => $user->user_phone,
                    'user_image' => ($user->image) ? $user->image->image_url : null,
                    'user_email' => $user->user_email,
                    'user_role' => $user->role->role_name,
                    'user_status' => $user->status->status_name,
                    'user_image' => ($user->image) ? $user->image->image_url : null,
                    'item_num'=>$item_num
                ],
            ];
            $this->code = 200;
            if($request->mobile_token)
            {

                $DevicesTokens=DevicesTokens::
                 where('user_id','=',$user->id)
                ->where('device_uuid','=',$request->device_uuid)
                ->where('role_id','=',$user->role_id)
                ->first();
                if(empty($DevicesTokens)){
                $DevicesTokenss=new DevicesTokens;

                $DevicesTokenss->user_id=$user->id;
                $DevicesTokenss->device_token=$request->mobile_token;
                $DevicesTokenss->device_uuid=$request->device_uuid;
                $DevicesTokenss->role_id=2;
                $DevicesTokenss->save();


                }
                else
                {
                    $DevicesTokens->device_token=$request->mobile_token;
                    $DevicesTokens->save();
                }
            }
        } catch (Exception $e) {
            $this->data['data'] = $e->getMessage();
            $this->data['code'] = 401;
        } catch (JWTException $e) {
            $this->data['data'] = 'Could not create token';
            $this->data['code'] = 500;
        }
        return response()->json($this->data, $this->code);
    }



    public function loginOwner(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_phone' => 'required',
            'password' => 'required|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'data' => $validator->messages(),
            ], 400);
        }

        $request->request->add(['role_id' => 2]);
        $credentials = $request->only(['user_phone', 'password','role_id']);
       //$credentials = $request->only(['user_phone', 'password']);
        
        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                throw new Exception('invalid_credentials');
            }
            
           $user = auth()->user();
            if($user->status_id!==1)
            {
                if($user->status_id==2)
                {
                    $sms_content = mt_rand(100000, 999999);

                    Otp::create(['user_id' => $user->id, 'verify_number' => $sms_content]);

                  
                    $sms_content = mt_rand(100000, 999999);
                    $curl = curl_init();
                    curl_setopt_array($curl, array(
                      CURLOPT_URL => 'https://oursms.app/api/v1/SMS/Add/SendOtpSms',
                      CURLOPT_RETURNTRANSFER => true,
                      CURLOPT_ENCODING => '',
                      CURLOPT_MAXREDIRS => 10,
                      CURLOPT_TIMEOUT => 0,
                      CURLOPT_FOLLOWLOCATION => true,
                      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                      CURLOPT_CUSTOMREQUEST => 'POST',
                      CURLOPT_POSTFIELDS =>'{
                        "userId":1,
                        "key": "123E6BCA5555D0DADF39A3D2958ECCB0",
                        "phoneNumber":"'.$request->user_phone.'",
                        "otp":"'.$sms_content.'"
                    }',
                      CURLOPT_HTTPHEADER => array(
                        'Content-Type: application/json',
                        'Cookie: ARRAffinity=d8317cc4cf9f66cc009ee7be5032f942a49d32e34d351a5e76312d2dabbe2c21; ARRAffinitySameSite=d8317cc4cf9f66cc009ee7be5032f942a49d32e34d351a5e76312d2dabbe2c21'
                      ),
                    ));
            
                    
                    $response = curl_exec($curl);
                    curl_close($curl);


                    return response()->json([
                        'success' => false,
                        'data' => 'User Unactive',
                        'token'=>$token
                    ],200);
                }
            }

        if($user->role->role_name=="owner")
        {
           $item_num=$user->owner->item->count();
        }
        else
        {
            $item_num='';

        }

            $this->data = [
                'sucess' => true,
                'data' => [
                    'id' => $user->id,
                    'token' => $token,
                    'id' => $user->id,
                    'user_name' => $user->full_name,
                    'user_phone' => $user->user_phone,
                    'user_image' => ($user->image) ? $user->image->image_url : null,
                    'user_email' => $user->user_email,
                    'user_role' => $user->role->role_name,
                    'user_status' => $user->status->status_name,
                    'user_image' => ($user->image) ? $user->image->image_url : null,
                    'item_num'=>$item_num
                ],
            ];
            $this->code = 200;
            if($request->mobile_token)
            {

                $DevicesTokens=DevicesTokens::
                 where('user_id','=',$user->id)
                ->where('device_uuid','=',$request->device_uuid)
                ->where('role_id','=',$user->role_id)
                ->first();
                if(empty($DevicesTokens)){
                $DevicesTokenss=new DevicesTokens;

                $DevicesTokenss->user_id=$user->id;
                $DevicesTokenss->device_token=$request->mobile_token;
                $DevicesTokenss->device_uuid=$request->device_uuid;
                $DevicesTokenss->role_id=2;
                $DevicesTokenss->save();


                }
                else
                {
                    $DevicesTokens->device_token=$request->mobile_token;
                    $DevicesTokens->save();
                }
            }
        } catch (Exception $e) {
            $this->data['data'] = $e->getMessage();
            $this->data['code'] = 401;
        } catch (JWTException $e) {
            $this->data['data'] = 'Could not create token';
            $this->data['code'] = 500;
        }
        return response()->json($this->data, $this->code);
    }



    public function loginOpertion(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'user_phone' => 'required',
            'password' => 'required|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'data' => $validator->messages(),
            ], 400);
        }

     $request->request->add(['role_id' =>3]);
        $credentials = $request->only(['user_phone', 'password','role_id']);
    
        
        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json([
                    'success' => false,
                    'data' => 'wrong',
                ], 400);
                throw new Exception('invalid_credentials');
            }
            
            $user = auth()->user();
            if($user->status_id!==1)
            {
              
                return response()->json([
                    'success' => false,
                    'data' => 'wrong',
                ],400);
            }

           
            //$this->sms();
              $this->data = [
          
            ];


            // $this->data = [
            //     'sucess' => true,
            //     'data' => [
            //         'id' => $user->id,
            //         'token' => $token,
            //         'id' => $user->id,
            //         'user_role' => $user->role->role_name,
            //         'user_name' => $user->full_name,
            //         'user_phone' => $user->user_phone,
            //         'user_image' => ($user->image) ? $user->image->image_url : null,
            //         'user_email' => $user->user_email,
            
                  
            //     ],
            // ];
            $this->code = 200;
           
        } catch (Exception $e) {
            $this->data['data'] = $e->getMessage();
            $this->data['code'] = 401;
        } catch (JWTException $e) {
            $this->data['data'] = 'Could not create token';
            $this->data['code'] = 500;
        }
        $sms_content = mt_rand(100000, 999999);
        $this->smsData['number'] =$user->user_phone;
        $this->smsData['text'] =$sms_content;
        $this->smsData['messageType'] = 3;
        $this->smsData['sentThrough'] = 1;
        Otp::create(['user_id' => $user->id, 'verify_number' => $sms_content]);

        // if($this->sms())
        // {
        //     return response()->json($this->data,$this->code);
        // }
      
    }


    public function OperationLoginOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_phone' => 'required|min:13|numeric',
            'otpNumber' =>'required|min:6|numeric',
            'password' => 'required|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'data' => $validator->messages(),
            ], 400);
        }
        $user = User::where('user_phone', $request->user_phone)->where('role_id','=',3)->first();

        if($user)
        {
          $otpObject = Otp::where('user_id','=',$user->id)
           // ->where('verify_number','=',$request->otpNumber)
           -> orderBy('id', 'desc')->first();
           
        //    if($otpObject->verify_number==$request->otpNumber)
        //    {
            $otpObjectlast= $otpObject;


        //    }
        //    else
        //    {
        //     return response()->json([
        //         'success' => false,
        //         'data' => 'Account Not Found',
        //     ], 401);

        //    }

        }
        else
        {
            return response()->json([
                'success' => false,
                'data' => 'Account Not Found',
            ], 401);

        }


        if (!empty($otpObjectlast)) {
            $this->code=200;
            $request->request->add(['role_id' =>3]);
            $credentials = $request->only(['user_phone', 'password','role_id']);
    
        
           
                if (!$token = JWTAuth::attempt($credentials)) {
                    return response()->json([
                        'success' => false,
                        'data' => 'wrong',
                    ], 400);
                    throw new Exception('invalid_credentials');
                }
            
            $this->data = [
                'success' => true,
                'data' => [
                    'id' => $user->id,
                    'token' => $token,
                    'id' => $user->id,
                    'user_role' => $user->role->role_name,
                    'user_name' => $user->full_name,
                    'user_phone' => $user->user_phone,
                    'user_image' => ($user->image) ? $user->image->image_url : null,
                    'user_email' => $user->user_email,
            
                  
                ],
            ];
              return    response()->json($this->data,$this->code);
              
            
        }
    
        else
        {
            return response()->json([
                'success' => false,
                'data' => 'otp invalid',
            ], 400);
        }
        
        
        
      
    }

    public function logout()
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
            return response()->json([
                'success' => true,
                'data' => 'Successfully logged out',
            ], 200);
        
        }

        catch (Exception $e) {
            return response()->json([
                'success' => false,
                'data' => 'wrong',
            ], 400);
        } 

        
     
    }


    public function changeStatus($statusId, $userId = null)
    {
        if (!$statusId) {
            return response()->json([
                'success' => false,
                'data' => 'status id missing',
            ], 401);
        }
        if (!$userId) {
            $user = JWTAuth::parseToken()->authenticate();
            $id = $user->id;
        } else {
            $id = $userId;
        }
        $u = User::find($id);
        $u->status_id = $statusId;
        $u->save();
        return response()->json([
            'success' => true,
            'data' => null,
        ], 200);

    }

    public function refresh(Request $request)
    {
        
        try {
            $token = JWTAuth::getToken();
            $new_token = JWTAuth::refresh($token);
            return response()->json([
                'success' => true,
                'data' => $new_token,
            ], 200);
        }

             catch (Exception $e) {
           $this->data['data'] = $e->getMessage();
           return response()->json([
            'success' => false,
            'data' => $this->data['data'] = $e->getMessage(),
        ], 500);
           
        } 

       

        
    }
    public function forgotPassword(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'user_phone' => 'required|min:13|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'data' => $validator->messages(),
            ], 400);
        }
        $user = User::where('user_phone', $request->user_phone)->first();
        if ($user) {

            //Otp
            $this->verifyNumber($user);
        }
        else
        {
            return response()->json([
                'success' => false,
                'data' => 'Account Not Found',
            ], 401);

        }
        
       
        return response()->json([
            'success' => true,
            'data' => 'message sent ....',
        ], 200);
    }


   
    public function DeleteImage($id)
    {
        $id=(int)$id;

      $user=JWTAuth::parseToken()->authenticate();
    
      $Image = Image::whereHasMorph('imageable', [User::class],
      function ($query) use($user) {
      $query->where('id', '=',$user->id) ;
       } ) ->where('id','=',$id) ->first();
    
     

        if($Image)
        {
            $Image->delete();
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
    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_phone' => 'required|min:13|numeric',
            'password' =>'required|min:8|confirmed',
            'otpNumber' =>'required|min:6|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'data' => $validator->messages(),
            ], 400);
        }
        $user = User::where('user_phone', $request->user_phone)->first();
     //   $user = JWTAuth::parseToken()->authenticate();
        if($user)
        {
            $otpObject = Otp::where('user_id','=',$user->id)
            ->where('verify_number','=',$request->otpNumber)
            ->get();

        }
        else
        {
            return response()->json([
                'success' => false,
                'data' => 'Account Not Found',
            ], 401);

        }


        if (count($otpObject)==0) {
            return response()->json([
                'success' => false,
                'data' => 'otp invalid',
            ], 400);
        }
        
        
        
        else {
            $end_date = strtotime(date("Y-m-d H:i:s"));
            $start_date = strtotime($otpObject[0]->created_at);
            $diffInHoures = floor(($end_date - $start_date) / 60 / 60);
            if ($diffInHoures >= 25) {
                return response()->json([
                    'success' => false,
                    'data' => 'otp expired',
                ], 401);
            }
        }
        $user = User::find($user->id);
        $user->password = app('hash')->make($request->password);
        $user->save();
        return response()->json([
            'success' => true,
            'data' => 'password changed',
        ], 200);
    }

}
