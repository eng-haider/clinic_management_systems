<?php

namespace App\Http\Controllers;
use Illuminate\Routing\Controller;
use Validator;
use App\OwnerCategory;
use Illuminate\Http\Request;
use App\Http\Controllers\UploadImage;
class OwnerCategoryController extends Controller
{

    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => Owner::with(['categories','owners'])->all()
        ], 200);
    }

    public function changeStatusOwnerCategory(Request $request){
        $this->validate($request,[
            'owner_id' => 'required',
            'status_id' => 'require',
            'category_id' => 'required'
        ]);
        $ownerCategory = OwnerCategory::where(['owner_id' => $request->owner_id ,'category_id' => $request->category_id]);
        $ownerCategory->status_id = $request->status_id;
        $ownerCategory->save();

        return response()->json([
            'success' => true,
            'data' => null
        ], 200);

    }


    public function create()
    {
        //
    }


    public function store(Request $request)
    {

    $this->validate($request,[
        'owner_id' => 'required',
        'status_id' => 'require',
        'category_id' => 'required'
    ]);
    try{
        $ownerCategory = new OwnerCategory;
        $ownerCategory->owner_id = $request->owner_id;
        $ownerCategory->category_id = $request->category_id;
        $owner->status_id = $request->status_id;
        $ownerCategory->save();
    }catch(\Illuminate\Database\Eloquent $e){
        return response()->json([
            'success' => false,
            'data' => null
        ], 400);
    }

    return response()->json([
        'success' => true,
        'data' => null
    ], 200);

    }

}
