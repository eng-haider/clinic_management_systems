<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use App\Models\Items;
class ItemsControllerAPI extends Controller
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
            'name' => 'required|string|between:2,100',

        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }

        $User=Items::create([
            'name'=>$request->name,
            'quantity'=>$request->quantity,
            'required_quantity'=>$request->required_quantity,
            'clinics_id'=>auth()->user()->clinic_id
        ]);


    }

    public function GetByClinicId(Request $request){

        return response()->json([
            'message' => 'successfully',
            'data'=>Items::where('clinics_id','=',auth()->user()->clinic_id)->get()
        ], 200);


    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
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

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|between:2,100',

        ]);


    $Item= Items::where('clinics_id','=',auth()->user()->Doctor->clinics_id)->where('id','=',$id)->get();
       $Item[0]->name=$request->name;
       $Item[0]->quantity=$request->quantity;
       //$Item[0]->required_quantity=$request->required_quantity;
       $Item[0]->save();



    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $Item= Items::where('clinics_id','=',auth()->user()->Doctor->clinics_id)->where('id','=',$id)->get();
        if(count($Item)>0){
            $Item[0]->delete();
            return response()->json([
                'message' => 'unsuccessfully',

            ], 200);
        }else{
            return response()->json([
                'message' => 'unsuccessfully',

            ], 500);
        }
    }
}
