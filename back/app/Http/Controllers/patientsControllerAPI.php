<?php

namespace App\Http\Controllers;

use App\Models\Patients;
use App\Models\User;
use App\Models\Doctors;
use App\Models\DoctorPatient;

use App\Http\Resources\patientsResource;




use App\Http\Controllers\Controller;
use App\Http\Controllers\CasesControllerAPI;

use App\Http\Requests\patientsRequest;
use App\Http\Resources\Collections\patientsCollection;

use Illuminate\Support\Facades\Auth;
use Validator;
use Illuminate\Http\Request;

class patientsControllerAPI extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return App\Http\Resources\Collections\patientsCollection
     */
    public function index()
    {
        //$this->authorize('viewAny', patients::class);

        $patients = patients::all();

        return ['data'=>new patientsCollection($patients),
        'case_counts'=>$patients->Cases->countt()

    ];

    }


    public function patientsAccounsts(patientsRequest $request){


        $user=User::where('clinic_id','=',auth()->user()->clinic_id)->where('role_id','=',2)->get();
        $doctor=Doctors::where('user_id','=',$user[0]->id)->get();
      $patients = patients::with(['Cases.Bills','Case.CaseCategories','Cases.CaseCategories'

      ])->where('doctor_id','=',$doctor[0]->id)->orderBy('id', 'desc')->paginate();



      return ['data'=>new patientsCollection($patients),
      'case_counts'=>$patients->Cases->countt()

  ];

    }




    //getByDoctor
    public function getByDoctor($Doctors)
    {

$DOC_ID=$Doctors;
          $user=User::where('clinic_id','=',auth()->user()->clinic_id)->where('role_id','=',5)->get();
          $doctor=Doctors::where('user_id','=',$user[0]->id)->get();
           $clinic_id=$doctor[0]->id;



            $Doctors=Doctors::where('clinics_id','=',$clinic_id)->get()->pluck('id');
            $patients = patients::with(['Case','Case.images','Case.Bills','Case.CaseCategories','Cases.Bills','user','Cases','Cases.CaseCategories','Doctors'])
            ->whereHas('Doctors', function ($query) use($DOC_ID)  {
                $query->where('doctors.id','=',$DOC_ID);
            })->orderBy('id', 'desc')->get();



        return new patientsCollection($patients);

    }
    public function getByUserId()
    {


          $user=User::where('clinic_id','=',auth()->user()->clinic_id)->where('role_id','=',2)->orwhere('role_id','=',5)->get();
          $doctor=Doctors::where('user_id','=',$user[0]->id)->get();
           $clinic_id=$doctor[0]->id;

           //Get patients to Doctors
          if(auth()->user()->role_id==2){
            $patients = patients::with(['Case','Case.images','Case.Bills','Case.CaseCategories','Cases.Bills','user','Cases','Cases.CaseCategories','Doctors'])
            ->whereHas('Doctors', function ($query) use($doctor)  {
                $query->where('doctors.id','=',auth()->user()->Doctor->id);
            })->orderBy('id', 'desc')->get();



            //Get patients to secretaries
          }else if(auth()->user()->role_id==3 ||auth()->user()->role_id==5){
            $Doctors=Doctors::where('clinics_id','=',$clinic_id)->get()->pluck('id');
            $patients = patients::with(['Case','Case.images','Case.Bills','Case.CaseCategories','Cases.Bills','user','Cases','Cases.CaseCategories','Doctors'])
            ->whereHas('Doctors', function ($query) use($Doctors)  {
                $query->whereIn('doctors.id',$Doctors);
            })->orderBy('id', 'desc')->get();
          }


        return new patientsCollection($patients);

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\patientsRequest  $request
     * @return \App\Http\Resources\patientsResource
     */

     public function testinCase(){




        foreach(patients::get() as $case){

            DoctorPatient::create(

                [
                'doctors_id'=>$case->doctor_id,
                'patients_id'=>$case->id,
                ]
            );
        }

     }

    public function store(patientsRequest $request)
    {


        $validator = Validator::make($request->all(), [
            'name' => 'required|string|between:2,100',

        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }

        $User=User::create([
            'name'=>$request->name,
            'phone'=>$request->phone,
            'role_id'=>4
        ]);

          $user=User::where('clinic_id','=',auth()->user()->clinic_id)->where('role_id','=',2)->orwhere('role_id','=',5)->get();
          $doctor=Doctors::where('user_id','=',$user[0]->id)->get();



        $patients = patients::create(array_merge(
                    $validator->validated(),
                    [
                    'name'=>$request->name,
                    'user_id'=>$User->id,
                    'age'=>$request->age,
                   // 'doctor_id'=>auth()->user()->Doctor->id,
                    'doctor_id'=>$doctor[0]->id,
                    'sex'=>$request->sex,
                    'phone'=>$request->phone,
                    'notes'=>$request->notes,
                    'systemic_conditions'=>$request->systemic_conditions,

                    ]
                ));




if(!isset($request->doctors[0])){

}

             //add DoctorPatient
            else if(is_object($request->doctors[0])){

             }
             else if(count($request->doctors)==0){
                $doctor=array(auth()->user()->Doctor->id);
                $patients->Doctors()->sync($doctor);
            }else{
                $doctors=$request->doctors;
                if(auth()->user()->role_id==2 && !in_array(auth()->user()->Doctor->id, $request->doctors)){

                    array_push($doctors,auth()->user()->Doctor->id);
                }

                $patients->Doctors()->sync($doctors);
            }



                return new patientsResource( $patients );

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\patients  $patients
     * @return \App\Http\Resources\patientsResource
     */

    public function search(patients $request,$search)
    {
        $user=User::where('clinic_id','=',auth()->user()->clinic_id)->where('role_id','=',2)->get();
        $doctor=Doctors::where('user_id','=',$user[0]->id)->get();
        $clinic_id=$doctor[0]->id;






      if(auth()->user()->role_id==2){
        $patients= patients::query()
        ->with(['Case','Case.images','Case.Bills','Case.CaseCategories','Cases.Bills','user','Cases','Cases.CaseCategories'])
        ->whereHas('Doctors', function ($query) use($doctor)  {
          $query->where('doctors.id','=',auth()->user()->Doctor->id);
      })

        ->where('name', 'LIKE',  '%' .$search .'%')
        ->orWhere('phone', 'LIKE',$search)
        ->get();


        //Get patients to secretaries
      }else if(auth()->user()->role_id==3 ||auth()->user()->role_id==5){
        $Doctors=Doctors::where('clinics_id','=',$clinic_id)->get()->pluck('id');
        $patients= patients::query()
        ->with(['Case','Case.images','Case.Bills','Case.CaseCategories','Cases.Bills','user','Cases','Cases.CaseCategories'])
        ->whereHas('Doctors', function ($query) use($Doctors)  {
          $query->whereIn('doctors.id',$Doctors);
      })

        ->where('name', 'LIKE',  '%' .$search .'%')
        ->orWhere('phone', 'LIKE',$search)
        ->get();
      }






        return new patientsResource($patients);



    }

    public function show(patients $patients)
    {
      ////  $this->authorize('view', $patients);

        return new patientsResource($patients);

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\patientsRequest  $request
     * @param  \App\Models\patients  $patients
     * @return \App\Http\Resources\patientsResource
     */


    public function update(patientsRequest $request, patients $patients)
    {

        $patient = patients::where('doctor_id','=',auth()->user()->Doctor->id)->where('id','=',$patients->id)->get();

        $patient[0]->name=$request->name;
        $patient[0]->email=$request->email;
        $patient[0]->age=$request->age;
        $patient[0]->phone=$request->phone;
        $patient[0]->sex=$request->sex;
        $patient[0]->systemic_conditions=$request->systemic_conditions;
        $patient[0]->notes=$request->notes;
        $patient[0]->save();

          //add DoctorPatient
          if(is_object($request->doctors[0])){

          }

        else if(count($request->doctors)==0){
            $doctor=array(auth()->user()->Doctor->id);
            $patient[0]->Doctors()->sync($doctor);
        }else{
            $patient[0]->Doctors()->sync($request->doctors);
        }


        return new patientsResource($patients);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\patients  $patients
     * @return \App\Http\Resources\patientsResource
     */
    public function destroy(patients $patients)
    {
      $patients = patients::where('doctor_id','=',auth()->user()->Doctor->id)->where('id','=',$patients->id)->get();

        $patients[0]->delete();

        return new patientsResource($patients);

    }
}
