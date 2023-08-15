<?php


namespace App\Http\Controllers\API;
use Validator;
use Illuminate\Routing\Controller;
use App\Country;
use Illuminate\Http\Request;

class CountryController extends Controller
{

    public function __construct()
    {
        $this->middleware('CheckRole:admin')->only(['destroy','update','store']);


    }

    public function index()
    {

        $all = Country::all();
        return response()->json([
            'success' => true,
            'data' => $all
        ], 200);
    }

    public function store(Request $request)
    {

            $validator = Validator::make($request->all(), [
                'country_name' => 'required',
                ]);

                if ($validator->fails()) {
                    return response()->json([
                        'success' => false,
                        'Data' =>  $validator->messages()
                    ], 400);

                    }

                $country = new country;
                $country->country_name = $request->country_name;
                $country->save();

                return response()->json([
                    'success' => true,
                    'data' => null
                ], 200);

    }

    public function show($id)
    {

        $province = Country::with('province')->where('id', $id)->get();
        return response()->json([
            'success' => true,
            'data' => $province
        ], 200);
    }

    public function update(Request $request,$id)
    {



       $validator = Validator::make($request->all(), [
        'country_name' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'Data' =>  $validator->messages()
            ], 400);

            }

         $country = Country::find($id);
         $country->country_name = $request->country_name;
         $country->save();

         return response()->json([
             'success' => true,
             'data' => null
         ], 200);
    }


    public function destroy(Request $request,$id)
    {

        $id=(int)$id;
        $country = Country::find($id)->delete();
        return response()->json([
            'success' => true,
            'data' => null
        ], 200);
    }
}
