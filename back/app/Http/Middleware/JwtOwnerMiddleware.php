<?php
namespace App\Http\Middleware;
use Closure;
use Exception;
use App\User;
use Illuminate\Support\Facades\DB;
use JWTAuth;

class JwtOwnerMiddleware{

    public function handle($request, Closure $next, $guard = null)
    {
        try {
         
            if (JWTAuth::check()) {
                throw new Exception('User Not Found');
            }
            elseif (JWTAuth::parseToken()->authenticate()->role->role_name !== 'owner') {
                throw new Exception('Unauthorized Permission');
            }
            elseif (JWTAuth::parseToken()->authenticate()->status_id == 2) {
               
                return response()->json([
                    'sucess' => false,
                    'data' => 'Unactive User',
                ], 401);
            }
            DB::commit();
            return $next($request);
           
      

        } catch (Exception $e) {

           
            if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException) {
                return response()->json([
                    'sucess' => false,
                    'data' => 'Token Invalid',
                ], 401);
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
                    ], 401);
                }elseif($e->getMessage() === 'Unauthorized Permission'){
                    return response()->json([
                        'sucess' => false,
                        'data' => 'Unauthorized Permission',
                    ], 401);
                }
             
              ;
            }
        }
        
    }


}
