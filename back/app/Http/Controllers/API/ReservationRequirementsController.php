<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\ReservationRequirements;
use App\Image;
use Validator;
use App\Http\Controllers\UploadImageController;
class ReservationRequirementsController extends Controller
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
    public function store($ss1,$reservation_id)
    {

      $ss=json_encode($ss1);
            $data = json_decode($ss);
         $data->item_reservation_requirements_id;
        $validator = Validator::make($ss1, [
            'item_reservation_requirements_id' => 'required|numeric',
           // 'reservation_id' => 'required|numeric',
            ]);


            if ($validator->fails()) {
                return false;
                return response()->json([
                    'success' => false,
                    'Data' =>  $validator->messages()
                ], 400);

                }


        $ReservationRequirements = new ReservationRequirements();
        $ReservationRequirements->item_reservation_requirements_id = $data->item_reservation_requirements_id;
        $ReservationRequirements->reservation_id = $reservation_id;
        $ReservationRequirements->save();


        if(count($data->images)!==0)
            {
            $upload = new UploadImageController;
            $path = $upload->uploadInnerReq($data);
            for($i = 0;$i < count($data->images);$i++){
                
             $ReservationRequirements->image()->create(
            [
                'image_url' =>$path[$i],
            ]);
            }


          }


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
