<?php

namespace App\Http\Controllers;

use App\Models\Doctors;
use App\Http\Resources\doctorsResource;
use App\Http\Controllers\Controller;
use App\Http\Requests\doctorsRequest;
use App\Http\Resources\Collections\doctorsCollection;
use App\Models\User;
use App\Models\Cases;
class doctorsControllerAPI extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return App\Http\Resources\Collections\doctorsCollection
     */
    public function index()
    {
        $this->authorize('viewAny', doctors::class);

        $doctors = doctors::all();

        return new doctorsCollection($doctors);

    }


    public function clinicDoctor()
    {
        $user=User::where('clinic_id','=',1)->where('role_id','=',2)->orwhere('role_id','=',5)->get();
        $doctor=Doctors::where('user_id','=',$user[0]->id)->get();
        $Doctor_id=$doctor[0]->id;

        $doctors=Doctors::where('clinics_id','=',$doctor[0]->clinics_id)->get();

        return new doctorsCollection($doctors);

    }


    public function clinicDoctorInfo()
    {
        $user=User::where('clinic_id','=',1)->where('role_id','=',2)->orwhere('role_id','=',5)->get();
        $doctor=Doctors::where('user_id','=',$user[0]->id)->get();
        $Doctor_id=$doctor[0]->id;


        $doctors=Doctors::where('clinics_id','=',$doctor[0]->clinics_id)->get();


        $array = [];
         //get all_sum
         for ($i=0;$i<count($doctors) ;$i++)
         {
            $object = (object) ['info' => 0,'all_sum' => 0, 'paid' => 0];
            $array[] = $object;


    }


         for ($i=0;$i<count($doctors) ;$i++)
      {

        $Doctor_id=$doctors[$i]->id;

        $doc=Doctors::withcount(['cases','Patients'])->where('id','=',$Doctor_id)->get();
        $array[$i]->info=$doc[0];




        $array[$i]->all_sum=$Cases=Cases::whereHas('Doctors', function ($query) use($Doctor_id) {
         $query->where('doctors.id','=',$Doctor_id);
        })->with('Bills')->get()->sum('price');



         $Cases=Cases::whereHas('Doctors', function ($query) use($Doctor_id) {
         $query->where('doctors.id','=',$Doctor_id);
         })->with('Bills')->get();



          //get paid
        $paid = 0;
        for ($x=0;$x<count($Cases) ;$x++)
        {
            for ($j=0;$j<count($Cases[$x]->Bills) ;$j++)
            {

                $paid+= $Cases[$x]->Bills[$j]->price;
            }


        }

//        return $paid;
        $array[$i]->paid=$paid;

    }

    return  $array;
        return new doctorsCollection($doctors);

    }
    //clinicDoctor

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\doctorsRequest  $request
     * @return \App\Http\Resources\doctorsResource
     */
    public function store(doctorsRequest $request)
    {
        $this->authorize('create', doctors::class);

        $doctors = doctors::create($request->validated());

        return new doctorsResource($doctors);

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\doctors  $doctors
     * @return \App\Http\Resources\doctorsResource
     */
    public function show(doctors $doctors)
    {
        $this->authorize('view', $doctors);

        return new doctorsResource($doctors);

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\doctorsRequest  $request
     * @param  \App\Models\doctors  $doctors
     * @return \App\Http\Resources\doctorsResource
     */
    public function update(doctorsRequest $request, doctors $doctors)
    {
        $this->authorize('update', $doctors);

        $doctors->update($request->validated());

        return new doctorsResource($doctors);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\doctors  $doctors
     * @return \App\Http\Resources\doctorsResource
     */
    public function destroy(doctors $doctors)
    {
        $this->authorize('delete', $doctors);

        $doctors->delete();

        return new doctorsResource($doctors);

    }
}
