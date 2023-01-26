<?php

namespace App\Http\Controllers;

use App\Models\Users;
use App\Http\Resources\UsersResource;
use App\Http\Controllers\Controller;
use App\Http\Requests\UsersRequest;
use Laravel\Sanctum\HasApiTokens;
use App\Http\Resources\Collections\UsersCollection;
use JWTAuth;
use Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class UsersControllerAPI extends Controller
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

        $user = Users::all();

        return new UsersCollection($user);

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

        return response()->json([
            'message' => 'successfully',
            'token' =>$token,
            'result'=>new UsersResource(auth()->Users())
        ], 200);
    }
    
    public function store(UsersRequest $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|between:2,100',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => 'required|string|min:6',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }

        $user= Users::create(array_merge(
                    $validator->validated(),
                    ['password' => bcrypt($request->password)]
                ));

        return response()->json([
            'message' => 'Users successfully registered',
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
