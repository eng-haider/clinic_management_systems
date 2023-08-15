<?php

namespace App\Http\Controllers\API;

use App\Brand;
use Validator;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Http\Controllers\UploadImageController;

class BrandController extends Controller
{

    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => Brand::all()
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'brand_name' => 'required',
            'brand_discripation' => '',
            'images' => ''

        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'Data' => $validator->messages(),
            ], 400);

        }

        $brand = new Brand;
        $brand->brand_name = $request->brand_name;
        $brand->brand_discripation = (!$request->brand_discripation)?$request->brand_discripation:'null';
        $brand->save();
        if(count($request->images)!==0)
        {
        $upload = new UploadImageController;
        $path = $upload->uploadInner($request);
        $brand->image()->create(
            [
                'image_url' =>$path[$i],
            ]);
        }
        return response()->json([
            'success' => true,
            'data' => null
        ], 200);
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'brand_id' => 'required',
            'brand_name' => 'required',
            'brand_discripation' => '',
            'images' => ''

        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'Data' => $validator->messages(),
            ], 400);

        }

        $brand = Brand::find($request->brand_id);
        $brand->brand_name = $request->brand_name;
        $brand->brand_discripation = (!$request->brand_discripation)?$request->brand_discripation:'null';
        $brand->save();
        if(count($request->images)!==0)
        {
        $upload = new UploadImageController;
        $path = $upload->uploadInner($request);
        $brand->image()->update(
            [
                'image_url' =>$path[$i],
            ]);
        }
        return response()->json([
            'success' => true,
            'data' => null
        ], 200);
    }

    public function destroy($brand_id)
    {
        $brand = Brand::find($brand_id)->delete();
        return response()->json([
            'success' => true,
            'data' => null
        ], 200);
    }
}
