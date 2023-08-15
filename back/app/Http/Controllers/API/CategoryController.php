<?php

namespace App\Http\Controllers\API;
use Validator;
use App\Category;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\CategoryCollection;
use App\Image;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\UploadImageController;
class CategoryController extends Controller
{

    public function __construct()
    {
      //  $this->middleware('jwt.auth.Operators')->only(['destroy','update','store']);
       $this->middleware('CheckRole:admin')->only(['destroy','update','store','DeleteImage']);
    }

    public function index()
    {


        $Categories= CategoryResource::Collection(Category::paginate(15));

        return response()->json([
            'success' => true,
            'data' =>$Categories
        ], 200);
    
    }

    public function getSubCategoryWithCategory()
    {
        $all = Category::with(['sub_category'])->get();
        return response()->json([
            'success' => true,
            'data' => $all
        ], 200);
    }


    public function show($id)
    {
        $id=(int)$id;
        $all =
         Category::with(['sub_category'])->where('id','=',$id)->get();

        return response()->json([
            'success' => true,
            'data' => $all
        ], 200);
    }

    public function store(Request $request)
    {


            $validator = Validator::make($request->all(), [
                'category_name' => 'required',
                'icon'=> 'required',
                ]);

                if ($validator->fails()) {
                    return response()->json([
                        'success' => false,
                        'Data' =>  $validator->messages()
                    ], 400);

                }

                $category = new Category;
                $category->category_name = $request->category_name;
                $category->icon = $request->icon;
                $category->save();

                if(count($request->images)!==0)
                {
                $upload = new UploadImageController;
                $path = $upload->uploadInner($request);
                for($i = 0;$i < count($request->images);$i++){
                    $category->image()->create(
                        [
                            'image_url' =>$path[$i],
                        ]);
                }
                    
    
                }
                

         return response()->json([
             'success' => true,
             'data' => null
         ], 200);
    }


    public function update(Request $request,$id)
    {
        $id=(int)$id;
        $validator = Validator::make($request->all(), [
            'category_name' => 'required',
            'icon'=> 'required',
            ]);


            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'Data' =>  $validator->messages()
                ], 400);

            }

         $category = Category::find($id);
         $category->category_name = $request->category_name;
         $category->icon = $request->icon;
         $category->save();


         if(count($request->images)!==0)
         {
         $upload = new UploadImageController;
         $path = $upload->uploadInner($request);
         for($i = 0;$i < count($request->images);$i++){
             $category->image()->create(
                 [
                     'image_url' =>$path[$i],
                 ]);
         }
             

         }

         return response()->json([
             'success' => true,
             'data' => null
         ], 200);
    }

    
    public function DeleteImage($id)
    {

      
      $Image = Image::whereHasMorph('imageable', [Category::class])->where('id','=',$id)

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
    public function destroy($id)
    {

        $id=(int)$id;
        $category = Category::find($id)->delete();
        return response()->json([
            'success' => true,
            'data' => null
        ], 200);
    }
}
