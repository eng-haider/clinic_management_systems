<?php

namespace App\Http\Controllers\API;

use App\LastCategory;
use Illuminate\Http\Request;
use Validator;
use Illuminate\Routing\Controller;
use App\Http\Resources\LastCategoryResource;
use App\Image;
use App\Http\Controllers\UploadImageController;

class LastCategoryController extends Controller
{
    public function __construct()
    {
      $this->middleware('CheckRole:admin')->only(['destroy','update','store']);
     
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    public function DeleteImage($id)
    {

      
      $Image = Image::whereHasMorph('imageable', [LastCategory::class])->where('id','=',$id)

        ->first();
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

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'last_category_name' => 'required',
            'sub_category_id' => 'required'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'Data' =>  $validator->messages()
                ], 400);

                }

        $sub = new LastCategory;
        $sub->last_category_name = $request->last_category_name;
        $sub->sub_category_id  = $request->sub_category_id ;
        $sub->save();

        if(count($request->images)!==0)
        {
        $upload = new UploadImageController;
        $path = $upload->uploadInner($request);
        for($i = 0;$i < count($request->images);$i++){
            $sub->image()->create(
                [
                    'image_url' =>$path[$i],
                ]);
        }
            

        }

        return response()->json([
            'success' => true  ,
            'date' => null
        ], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\LastCategory  $lastCategory
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

        $sub = LastCategory::where('id', $id)
           ->orderBy('last_category_name', 'desc')
           ->get();
           return response()->json([
            'success' => true  ,
            'date' =>  $sub
        ], 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\LastCategory  $lastCategory
     * @return \Illuminate\Http\Response
     */
    public function edit(LastCategory $lastCategory)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\LastCategory  $lastCategory
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request,$id)
    {
    
        $id=(int)$id;


        $validator = Validator::make($request->all(), [
            'last_category_name' => 'required',
            'sub_category_id' => 'required'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'Data' =>  $validator->messages()
                ], 400);

                }

        $sub = LastCategory::find($id);
        $sub->last_category_name = $request->last_category_name;
        $sub->sub_category_id = $request->sub_category_id;
        $sub->save();


        if(count($request->images)!==0)
        {
        $upload = new UploadImageController;
        $path = $upload->uploadInner($request);
        for($i = 0;$i < count($request->images);$i++){
            $sub->image()->create(
                [
                    'image_url' =>$path[$i],
                ]);
        }
            

        }

        return response()->json([
            'success' => true  ,
            'date' => null
        ], 200);
    }

    public function GetBySubCategoryId($id)
    {
        
        $id=(int)$id;
 
        $all = LastCategoryResource::collection(LastCategory::where('sub_category_id','=',$id)->get());
        return response()->json([
            'success' => true,
            'data' =>$all
        ], 200);

    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\LastCategory  $lastCategory
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $id=(int)$id;

        $sub = LastCategory::find($id)->delete();

        return response()->json([
            'success' => true  ,
            'date' => null
        ], 200);
    }
}
