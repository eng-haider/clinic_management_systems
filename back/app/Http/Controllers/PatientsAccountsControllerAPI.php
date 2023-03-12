<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\patientsAccountsResource;
use App\Http\Requests\patientsRequest;
use App\Models\Patients;
use App\Models\Cases;
use App\Models\User;

use App\Models\Doctors;
use App\Http\Resources\Collections\PatientsAccountsCollection;


class PatientsAccountsControllerAPI extends Controller
{
    public function patientsAccounsts(patientsRequest $request){


      // $user=User::where('clinic_id','=',auth()->user()->clinic_id)->where('role_id','=',2)->get();
      // $doctor=Doctors::where('user_id','=',$user[0]->id)->get();
      // $patients = patients::with(['Cases.Bills','Case.CaseCategories','Cases.CaseCategories'
      
      // ])->where('doctor_id','=',$doctor[0]->id)->orderBy('id', 'desc')->paginate();

      // return new PatientsAccountsCollection($patients);

          
      $user=User::where('clinic_id','=',auth()->user()->clinic_id)->where('role_id','=',2)->get();
      $doctor=Doctors::where('user_id','=',$user[0]->id)->get();
      $Doctor_id=$doctor[0]->id;

      
      $patients = patients::with(['Cases.Bills','Case.CaseCategories','Cases.CaseCategories'  ])   
      ->where('doctor_id','=',$doctor[0]->id)->orderBy('id', 'desc')->get();



    //get cases counts
      $Cases_count=Cases ::whereHas('Patient', function ($query) use($Doctor_id) {
        $query->where('doctor_id','=',$Doctor_id);
      })->with('Bills')->count();

       //get cases 
    $Cases=Cases
      ::whereHas('Patient', function ($query) use($Doctor_id) {
        $query->where('doctor_id','=',$Doctor_id);
      })->with('Bills')->get();



      //get all_sum 
      $all_sum=$Cases=Cases::whereHas('Patient', function ($query) use($Doctor_id) {
        $query->where('doctor_id','=',$Doctor_id);
      })->with('Bills')->get()->sum('price');

      $Cases=Cases::whereHas('Patient', function ($query) use($Doctor_id) {
        $query->where('doctor_id','=',$Doctor_id);
      })->with('Bills')->get();

      
       
      //get paid 
      $paid = 0;
      for ($i=0;$i<count($Cases) ;$i++)
      {
        for ($j=0;$j<count($Cases[$i]->Bills) ;$j++)
        {
        
          $paid += $Cases[$i]->Bills[$j]->price;
        }
 

      }
    
   

      return[
        'data'=>$patients,
        'case_count'=>$Cases_count,
        'all_sum'=>$all_sum,
        'paid'=>$paid,
        'remainingamount'=>$all_sum-$paid
      ];


    





    }


    public function search(patientsRequest $request){



      
      $user=User::where('clinic_id','=',auth()->user()->clinic_id)->where('role_id','=',2)->get();
      $doctor=Doctors::where('user_id','=',$user[0]->id)->get();
      $Doctor_id=$doctor[0]->id;


      $patients = patients:: whereBetween('created_at', [$request->from_date,$request->to_date])   
      ->with(['Cases.Bills','Case.CaseCategories','Cases.CaseCategories'  ])   
      ->where('doctor_id','=',$doctor[0]->id)->orderBy('id', 'desc')->get();



    //get cases counts
      $Cases_count=Cases ::whereHas('Patient', function ($query) use($Doctor_id) {
        $query->where('doctor_id','=',$Doctor_id);
      })->whereBetween('created_at', [$request->from_date,$request->to_date])->with('Bills')->count();

       //get cases 
    $Cases=Cases
      ::whereHas('Patient', function ($query) use($Doctor_id) {
        $query->where('doctor_id','=',$Doctor_id);
      })->
      whereBetween('created_at', [$request->from_date,$request->to_date])->with('Bills')->get();



      //get all_sum 
      $all_sum=$Cases=Cases::whereHas('Patient', function ($query) use($Doctor_id) {
        $query->where('doctor_id','=',$Doctor_id);
      })->whereBetween('created_at', [$request->from_date,$request->to_date])->with('Bills')->get()->sum('price');

      $Cases=Cases::whereHas('Patient', function ($query) use($Doctor_id) {
        $query->where('doctor_id','=',$Doctor_id);
      })->whereBetween('created_at', [$request->from_date,$request->to_date])->with('Bills')->get();

      
       
      //get paid 
      $paid = 0;
      for ($i=0;$i<count($Cases) ;$i++)
      {
        for ($j=0;$j<count($Cases[$i]->Bills) ;$j++)
        {
        
          $paid += $Cases[$i]->Bills[$j]->price;
        }
 

      }
    
   

      return[
        'data'=>$patients,
        'case_count'=>$Cases_count,
        'all_sum'=>$all_sum,
        'paid'=>$paid,
        'remainingamount'=>$all_sum-$paid
      ];


    


    }
}
