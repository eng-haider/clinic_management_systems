<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\patientsAccountsResource;
use App\Http\Requests\patientsRequest;
use App\Models\Patients;
use App\Models\User;
use App\Models\Doctors;
use App\Http\Resources\Collections\PatientsAccountsCollection;


class PatientsAccountsControllerAPI extends Controller
{
    public function patientsAccounsts(patientsRequest $request){


      $user=User::where('clinic_id','=',auth()->user()->clinic_id)->where('role_id','=',2)->get();
      $doctor=Doctors::where('user_id','=',$user[0]->id)->get();
      $patients = patients::with(['Cases.Bills','Case.CaseCategories','Cases.CaseCategories'
      
      ])->where('doctor_id','=',$doctor[0]->id)->orderBy('id', 'desc')->paginate();

      return new PatientsAccountsCollection($patients);



    }


    public function search(patientsRequest $request){


      $user=User::where('clinic_id','=',auth()->user()->clinic_id)->where('role_id','=',2)->get();
      $doctor=Doctors::where('user_id','=',$user[0]->id)->get();
      $patients = patients::whereBetween('created_at', [$request->from_date,$request->to_date])->with(['Cases.Bills','Case.CaseCategories','Cases.CaseCategories'
      
      ])->where('doctor_id','=',$doctor[0]->id)->orderBy('id', 'desc')->paginate();

      return new PatientsAccountsCollection($patients);



    }
}
