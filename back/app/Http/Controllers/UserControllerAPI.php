<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Resources\UserResource;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use Laravel\Sanctum\HasApiTokens;
use App\Http\Resources\Collections\UserCollection;
use JWTAuth;
use Validator;
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
            'email' => 'required|email',
            'password' => 'required|string|min:2',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

       
 
        if (! $token = auth()->attempt($validator->validated())) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        if(auth()->user()->role_id==2){
           $response = Http::post('https://tctate.com/api/api/auth/v2/loginOwner',['user_phone' =>auth()->user()->phone, 'password' =>$request->password]);
         
         
               $response = json_decode($response, true);
               if($response['success']=='true')
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
            'result'=>auth()->user(),
            'Permissions'=>auth()->user()->Role->Permissions
        ], 200);
    }
    public function store(UserRequest $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|between:2,100',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => 'required|string|min:6',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }

        $user = User::create(array_merge(
                    $validator->validated(),
                    ['password' => bcrypt($request->password), 'role_id'=>1]
                ));

        return response()->json([
            'message' => 'User successfully registered',
            'user' => $user
        ], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \App\Http\Resources\UserResource
     */
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