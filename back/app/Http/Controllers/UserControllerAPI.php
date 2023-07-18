<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Clinics;
use App\Models\Doctors;




use App\Http\Resources\UserResource;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use Laravel\Sanctum\HasApiTokens;
use App\Http\Resources\Collections\UserCollection;
use JWTAuth;
use Validator;

use App\Http\Resources\UsersResource;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class UserControllerAPI extends Controller
{

    public function __construct()
    {
        $this->middleware('CheckRole:admin')->only(['destroy','update']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return App\Http\Resources\Collections\UserCollection
     */
    public function index()
    {
       // $this->authorize('viewAny', User::class);

        $user = User::all();

        return new UserCollection($user);

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\UserRequest  $request
     * @return \App\Http\Resources\UserResource
     */
    public function login(Request $request){
    	$validator = Validator::make($request->all(), [
            'phone' => 'required',
            'password' => 'required|string|min:2',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }



        $credentials = $request->only('phone', 'password');
        $token = auth()->attempt($credentials);

        if (! $token) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }




        if(auth()->user()->role_id==2 ||auth()->user()->role_id==5){


           $response = Http::post('https://tctate.com/api/api/auth/v2/loginOwnerSmart',['user_phone' =>auth()->user()->phone, 'password' =>$request->password]);


               $response = json_decode($response, true);
               if($response['success']==true)
         {
            $tctate_token=$response['data']['token'];
            $User = User::where('id','=',auth()->user()->id)->get();
            $User[0]->tctate_token=$tctate_token;

            $User[0]->save();
         }
         else
         {
             $tctate_token='';
         }




        }
        else{
            $tctate_user=User::where('clinic_id','=',auth()->user()->clinic_id)->where('role_id','=',2)->get();
            $tctate_token=$tctate_user[0]->tctate_token;

        }

        return response()->json([
            'message' => 'successfully',
            'token' =>$token,
            'tctate_token'=>$tctate_token,
            'result'=>new UsersResource(auth()->user()),
            'doc_info'=>auth()->user()->role_id==2?auth()->user()->Doctor:auth()->user()->secretary,
            'clinic_info'=>auth()->user()->role_id==2 || auth()->user()->role_id==5?auth()->user()->Doctor->Clinics:auth()->user()->secretary->Clinics,
            'Permissions'=>auth()->user()->Role->Permissions
        ], 200);
    }

    public function getInfo()
    {

        return response()->json([
            'message' => 'successfully',
            'data'=>new UserResource(auth()->user())
        ], 200);

    }


    //DeleteImage

    public function DeleteImage()
    {

        $user=auth()->user();

          User::where('id', $user->id)->update([
            'img_name' =>null
        ]);

        $user=User::find($user->id);
        return response()->json([
           'message' => 'successfully',
           'data'=>new UserResource($user)
       ], 200);




    }

    public function UpdateInfo(UserRequest $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|between:2,100',
            // 'email' => 'required|string|email|max:100|unique:users'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }
        $user=auth()->user();
        User::where('id', $user->id)->update([
            'name' => $request->name
        ]);

        $user->Doctor->Clinics->update([
            'name' => $request->clinics_name
        ]);

         if(count($request->images)>0){
          $Data = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '',$request->images[0]));
          $img_name    ='img'.time().'.jpg';
          $destination ='users/'.$img_name;

         $user= User::where('id', $user->id)->update([
            'img_name' => $img_name
        ]);


            file_put_contents( $destination , $Data);
         }

         $user=User::find($user);
         return response()->json([
            'message' => 'successfully',
            'data'=>new UserResource($user)
        ], 200);
    }
    public function store(UserRequest $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|between:2,100',
            'phone' => 'required|numeric|min:11|unique:users',
            'password' => 'required|string|min:6',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }



        $Clinics=Clinics::create([
            'name'=>$request->clinic_name,

        ]);

        $user = User::create(array_merge(
                    $validator->validated(),
                    ['password' => bcrypt($request->password), 'role_id'=>2,'clinic_id'=>$Clinics->id]
                ));

                $token_sm =JWTAuth::fromUser($user);



                $doctors=Doctors::create([
                    'name'=>$request->name,
                    'clinics_id'=>$Clinics->id,
                    'user_id'=>$user->id,
                ]);

                $response = Http::post('https://tctate.com/api/api/auth/v2/signUpOthers',[
                 'category_id' =>4,
                 'full_name' =>$request->name,
                 'owner_brand_name'=>$request->clinic_name,
                 'owner_phone'=>$request->phone,
                 'password'=>$request->password,
                 'password_confirmation'=>$request->password,
                 'user_phone'=>$request->phone,

                ]);
                $response=json_decode($response, true);;

                $token=$response['token'];

                    $this->addtctateItem($request->name,$request->phone,$token);

             //  $response = json_decode($response, true);

             if($user->role_id==2 ||$user->role_id==5){


                $response = Http::post('https://tctate.com/api/api/auth/v2/loginOwnerSmart',['user_phone' =>$user->phone, 'password' =>$request->password]);


                    $response = json_decode($response, true);
                    if($response['success']==true)
              {
                 $tctate_token=$response['data']['token'];
                 $User = User::where('id','=',$user->id)->get();
                 $User[0]->tctate_token=$tctate_token;

                 $User[0]->save();
              }
              else
              {
                  $tctate_token='';
              }




             }
             else{
                 $tctate_user=User::where('clinic_id','=',$user->clinic_id)->where('role_id','=',2)->get();
                 $tctate_token=$tctate_user[0]->tctate_token;

             }



             return response()->json([
                'message' => 'successfully',
                'token' =>$token_sm,
                'tctate_token'=>$tctate_token,
                'result'=>new UsersResource($user),
                'doc_info'=>$user->role_id==2?$user->Doctor:$user->secretary,
                'clinic_info'=>$user->role_id==2 || $user->role_id==5?$user->Doctor->Clinics:$user->secretary->Clinics,
                'Permissions'=>$user->Role->Permissions
            ], 200);


    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \App\Http\Resources\UserResource
     */




    public function addtctateItem($name,$phone,$token)
    {





        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://tctate.com/api/api/v2/items/storex',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS =>'{
            "item_name": "teed",
            "item_description": "..",
            "sub_category_id": 2,
            "last_category_id": 3,
            "category_id": 4,
            "price": {
                "id": "",
                "price_value": "22",
                "payment": "",
                "is_tasdid_payment": "",
                "is_zaincash_payment": "",
                "is_dollars": 1,
                "deduction": 0,
                "payment_when_receiving": 3,
                "free_cancellation": 0,
                "payment_after_awhile": "",
                "installments": "",
                "cancellation_deduction_ratio": "0"
            },
            "Address": {
                "address_descraption": "sa",
                "province": {
                    "id": 4,
                    "country_id": 1
                },
                "long": "",
                "lat": ""
            },
            "work_times": [
                {
                    "id": "",
                    "work_day": "0",
                    "every_day": 1,
                    "status": {
                        "id": "",
                        "status_name_ar": ""
                    },
                    "reservation_type": {
                        "id": "",
                        "reservation_type_name": ""
                    },
                    "reservations_number": "",
                    "time_to_open": []
                },
                {
                    "id": "",
                    "work_day": "1",
                    "get_response": "",
                    "every_day": 0,
                    "status": {
                        "id": "",
                        "status_name": ""
                    },
                    "reservation_type": {
                        "id": "",
                        "reservation_type_name": ""
                    },
                    "reservations_number": "",
                    "time_to_open": []
                },
                {
                    "id": "",
                    "work_day": "2",
                    "every_day": 0,
                    "reservation_type": {
                        "id": "",
                        "reservation_type_name": ""
                    },
                    "status": {
                        "id": "",
                        "status_name_ar": ""
                    },
                    "reservations_number": "",
                    "time_to_open": []
                },
                {
                    "id": "",
                    "every_day": 0,
                    "work_day": "3",
                    "reservation_type": {
                        "id": "",
                        "reservation_type_name": ""
                    },
                    "reservations_number": "",
                    "status": {
                        "id": "",
                        "status_name_ar": ""
                    },
                    "time_to_open": []
                },
                {
                    "id": "",
                    "work_day": "4",
                    "every_day": 0,
                    "reservation_type": {
                        "id": "",
                        "reservation_type_name": ""
                    },
                    "reservations_number": "",
                    "status": {
                        "id": "",
                        "status_name_ar": ""
                    },
                    "time_to_open": []
                },
                {
                    "id": "",
                    "work_day": "5",
                    "every_day": 0,
                    "reservation_type": {
                        "id": "",
                        "reservation_type_name": ""
                    },
                    "status": {
                        "id": "",
                        "status_name_ar": ""
                    },
                    "reservations_number": "",
                    "time_to_open": []
                },
                {
                    "id": "",
                    "every_day": 0,
                    "work_day": "6",
                    "reservation_type": {
                        "id": "",
                        "reservation_type_name": ""
                    },
                    "reservations_number": "",
                    "status": {
                        "id": "",
                        "status_name_ar": ""
                    },
                    "time_to_open": []
                }
            ],
            "status_id": 12,
            "ItemFeatures": [],
            "address_id": []
        }',
          CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'Authorization: Bearer '.$token
          ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
       // echo $response;







        $response=json_decode($response, true);;



        $curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://tctate.com/api/api/reservation/owner/set',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS =>'{
    "item_id": '.$response['item_id'].',
    "user": {
        "name": "test",
        "phone": "9647811111111"
    },
    "reservation_start_date": "2023-03-09",
    "reservation_date": "19999-01-09",
    "item_features": [],
    "withoutBills": 0,
    "reservation_end_date": "2023-03-09",
    "reservation_from_time": "01:00",
    "reservation_to_time": "02:59",
    "reservation_number": 1,
    "deliverable": false,
    "phone": "",
    "ReservationRequirements": [],
    "sub_time_id": ""
}',
  CURLOPT_HTTPHEADER => array(
    'Content-Type: application/json',
    'Authorization: Bearer '.$token
  ),
));

$response = curl_exec($curl);

curl_close($curl);
return true;
    }
    public function show(User $user)
    {
        $this->authorize('view', $user);

        return new UserResource($user);

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UserRequest  $request
     * @param  \App\Models\User  $user
     * @return \App\Http\Resources\UserResource
     */
    public function update(UserRequest $request, User $user)
    {
        $this->authorize('update', $user);

        $user->update($request->validated());

        return new UserResource($user);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User  $user
     * @return \App\Http\Resources\UserResource
     */
    public function destroy(User $user)
    {
        $this->authorize('delete', $user);

        $user->delete();

        return new UserResource($user);

    }
}
