<?php

namespace App\Http\Controllers\API;
use Illuminate\Routing\Controller;
use Validator;
use App\Status;
use Illuminate\Http\Request;

class StatusController extends Controller
{

    public function index()
    {
        return response()->json([
            'success' => true ,
                'data' => Status::all()
            ], 200);
    }

    public function getByUser(){
     $statuses = Status::whereHas('status_type',function($query){
         $query->where('id',1);
     })->get();
     return response()->json([
        'success' => true ,
            'data' => $statuses
        ], 200);
    }


    public function GetStatusByStatusType($id)
    {
        $id=(int)$id;
        $status=Status::where('status_type_id','=',$id)->get();
        return response()->json([
            'success' => true ,
            'data' =>$status
        ], 200);

    }
    public function store(Request $request)
    {
        $this->validate($request,[
            'status_name' => 'required'
        ]);

        $status = new Status;
        $status->status_name = $request->status_name;
        $status->save();
        return response()->json([
            'success' => true ,
            'data' => null
        ], 200);

    }


    public function update(Request $request)
    {
        $this->validate($request,[
            'status_id' => 'required',
            'status_name' => 'required'
        ]);

        $status = status::find($request->status_id);
        $status->status_name = $request->status_name;
        $status->save();
        return response()->json([
            'success' => true ,
            'data' => null
        ], 200);
    }

    public function destroy(Request $request)
    {
        $this->validate($request,[
            'status_id' => 'required'
        ]);

        $status = status::find($request->status_id)->delete();
        return response()->json([
            'success' => true ,
            'data' => null
        ], 200);
    }

}
