<?php

namespace App\Http\Controllers\API;
use Validator;
use App\SubCategory;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Http\Resources\SubCategoryResource;
use App\Image;
use App\Http\Controllers\UploadImageController;
class SubCategoryController extends Controller
{

    public function __construct()
    {
      $this->middleware('CheckRole:admin')->only(['destroy','update','store','DeleteImage']);
      // $this->middleware('jwt.auth.admin')->only(['destroy','update','store']);
    }

    public function index()
    {

    return    $all = SubCategoryResource::collection(SubCategory::all());

    }

    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'sub_category_name' => 'required',
            'category_id' => 'required'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'Data' =>  $validator->messages()
                ], 400);

                }

        $sub = new SubCategory;
        $sub->sub_category_name = $request->sub_category_name;
        $sub->category_id = $request->category_id;
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

    public function show($id)
    {

      
        $sub = SubCategory::where('id', $id)
           ->orderBy('sub_category_name', 'desc')
           ->get();
           return SubCategoryResource::collection($sub);
    }


    public function update(Request $request,$id)
    {
        $id=(int)$id;


        $validator = Validator::make($request->all(), [
            'sub_category_name' => 'required',
            'category_id' => 'required'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'Data' =>  $validator->messages()
                ], 400);

                }

        $sub = SubCategory::find($id);
        $sub->sub_category_name = $request->sub_category_name;
        $sub->category_id = $request->category_id;
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


    public function DeleteImage($id)
    {

      
      $Image = Image::whereHasMorph('imageable', [SubCategory::class])->where('id','=',$id)

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

    public function GetByCategoryId($id)
    {
        
        $id=(int)$id;
 
        $all = SubCategoryResource::collection(SubCategory::where('category_id','=',$id)->get());
        return response()->json([
            'success' => true,
            'data' =>$all
        ], 200);

    }

    public function destroy($id)
    {
        $id=(int)$id;

        $sub = SubCategory::find($id)->delete();

        return response()->json([
            'success' => true  ,
            'date' => null
        ], 200);
    }
}
