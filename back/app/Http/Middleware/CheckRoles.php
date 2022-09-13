<?php

namespace App\Http\Middleware;
use Exception;
use Closure;
use Illuminate\Http\Request;
use JWTAuth;
use Illuminate\Support\Facades\DB;
class CheckRoles
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next,$role)
    {

        
        //return $request->header();
        $role;
        $roles=[
            'admin'=>[1],
            'doctor'=>[2],
            'secretary'=>[3],
        
        ];

       $rolesId=$roles[$role] ?? [];
       try {
         
        if (JWTAuth::check()) {
            throw new Exception('User Not Found');
        }

        // if (JWTAuth::parseToken()->authenticate()->status_id ==2) {   
        //     return response()->json([
        //         'sucess' => false,
        //         'data' => 'Unactive User',
        //     ], 401);
        // }

        if(!in_array(JWTAuth::parseToken()->authenticate()->role->id,$rolesId))
        {
                  return response()->json([
                    'sucess' => false,
                    'data' => 'Unauthorized Permission',
                ], 400);
        }

        
        DB::commit();
        return $next($request);
    

            } catch (Exception $e) {

                    
                if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException) {
                    return response()->json([
                        'sucess' => false,
                        'data' => 'Token Invalid',
                    ], 402);
                } else if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException) {

                    return response()->json([
                        'sucess' => false,
                        'data' => 'Token Expired',
                    ], 401);
                } else {
                    if ($e->getMessage() === 'User Not Found') {

                        return response()->json([
                            'sucess' => false,
                            'data' => 'User Not Found',
                        ], 402);
                    }elseif($e->getMessage() === 'Unauthorized Permission'){
                        return response()->json([
                            'sucess' => false,
                            'data' => 'Unauthorized Permission',
                        ], 402);
                    }
                    else
                    {
                        return response()->json([
                            'sucess' => false,
                            'data' => 'some_error',
                        ], 402);

                    }
                
                ;
                }
            }
    }
}
