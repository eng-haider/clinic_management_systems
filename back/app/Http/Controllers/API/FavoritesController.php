<?php

namespace App\Http\Controllers\API;
use Validator;
use App\Item;
use App\Favorite;
use JWTAuth;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Resources\FavoritesResource;
use App\Http\Resources\FavoritesCollection;


class FavoritesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeFavoriteItem(Request $request)
    {
        $validator=Validator::make($request->all(), ['favoriteable_id' => 'required|numeric']);
    
            if ($validator->fails()) {
                return response()->json([
                'success' => false,
                 'data' =>  $validator->messages()
                ], 400);
                }

           $item=Item::where('id','=',$request->favoriteable_id)->get() ;               
   
            $itemFavoritesOld=Favorite::whereHasMorph('favoriteable',[Item::class])
            ->where('favoriteable_id','=',$request->favoriteable_id)
            ->where('user_id','=',JWTAuth::parseToken()->authenticate()->id)
            ->get(); 


            if(count($itemFavoritesOld)>0){
                return response()->json([
                    'success' => false,
                    'data' => 'itemFavoritesIsFfound',
                
                ], 200);   
            }
            if(count($item)>0)
            {
                $favorites=$item[0]->favorite()->create(
                    [
                    'user_id'=>JWTAuth::parseToken()->authenticate()->id,
                    
                ]);
                return response()->json([
                    'success' => true,
                    'data' => null,
                
                ], 200);

                }
                else
                {
                    return response()->json([
                        'success' => false,
                        'data' => null,
                    
                    ], 400);
                }
               



                
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

     public function GetUserFavorties()
     {

        $Favorite=Favorite::where('user_id','=',JWTAuth::parseToken()->authenticate()->id)->get();
         return new  FavoritesCollection($Favorite);
        // return response()->json([
        //     'success' => false,
        //     'data' => $Favorite,
        
        // ], 200);


        

     }
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
