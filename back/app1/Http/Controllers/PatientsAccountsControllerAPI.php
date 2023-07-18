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
use App\Exports\BillsExport;
use Maatwebsite\Excel\Facades\Excel;



class PatientsAccountsControllerAPI extends Controller
{


  public function export()
  {

    $user=User::where('clinic_id','=',auth()->user()->clinic_id)->where('role_id','=',2)->get();
    $doctor=Doctors::where('user_id','=',$user[0]->id)->get();
    $Doctor_id=$doctor[0]->id;


     return (new BillsExport($Doctor_id))->download('Logs.xlsx');

  }


  public function patientsAccounstsdash(patientsRequest $request){



    $user=User::where('clinic_id','=',auth()->user()->clinic_id)->where('role_id','=',2)->orwhere('role_id','=',5)->get();
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





  $obj3 = (object) [
    'name' => 'المبلغ المدفوع',
    'count' => $paid,
];

$obj4 = (object) [
  'name' => 'المبلغ المتبقي ',
  'count' => $all_sum-$paid,
];



   $myArray = [ $obj3, $obj4];

  return json_encode($myArray);












  }

     public function testin(){




        foreach(Cases::with('Patient')->get() as $case){

            CaseDoctor::create(

                [
                'doctors_id'=>$case->Patient->doctor_id,
                'cases_id'=>$case->id,
                ]
            );
        }

     }



     //getByDoctor



     public function getByDoctor(patientsRequest $request,$Doctor){





        $user=User::where('clinic_id','=',auth()->user()->clinic_id)->where('role_id','=',2)->orwhere('role_id','=',5)->get();
        $doctor=Doctors::where('user_id','=',$user[0]->id)->get();
        $Doctor_id=$Doctor;


        $patients = patients::with(['Cases.Bills','Case.CaseCategories','Cases.CaseCategories'  ])
      //  ->where('doctor_id','=',$doctor[0]->id)->orderBy('id', 'desc')

        ->whereHas('Cases.Doctors', function ($query) use($Doctor_id) {
          $query->where('doctors.id','=',$Doctor_id);
      })




        ->get();



      //get cases counts
        $Cases_count=Cases::whereHas('Doctors', function ($query) use($Doctor_id) {
          $query->where('doctors.id','=',$Doctor_id);
      })->with('Bills')->count();

         //get cases
      $Cases=Cases
        ::whereHas('Patient', function ($query) use($Doctor_id) {
          $query->where('doctor_id','=',$Doctor_id);
        })->with('Bills')->get();



        //get all_sum
        $all_sum=$Cases=Cases::whereHas('Doctors', function ($query) use($Doctor_id) {
          $query->where('doctors.id','=',$Doctor_id);
      })->with('Bills')->get()->sum('price');

        $Cases=Cases::whereHas('Doctors', function ($query) use($Doctor_id) {
          $query->where('doctors.id','=',$Doctor_id);
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



    public function patientsAccounsts(patientsRequest $request){





      $user=User::where('clinic_id','=',auth()->user()->clinic_id)->where('role_id','=',2)->orwhere('role_id','=',5)->get();
      $doctor=Doctors::where('user_id','=',$user[0]->id)->get();
      $Doctor_id=$doctor[0]->id;


      $patients = patients::with(['Cases.Bills','Case.CaseCategories','Cases.CaseCategories'  ])
    //  ->where('doctor_id','=',$doctor[0]->id)->orderBy('id', 'desc')

      ->whereHas('Cases.Doctors', function ($query) use($Doctor_id) {
        $query->where('doctors.id','=',$Doctor_id);
    })




      ->get();



    //get cases counts
      $Cases_count=Cases::whereHas('Doctors', function ($query) use($Doctor_id) {
        $query->where('doctors.id','=',$Doctor_id);
    })->with('Bills')->count();

       //get cases
    $Cases=Cases
      ::whereHas('Patient', function ($query) use($Doctor_id) {
        $query->where('doctor_id','=',$Doctor_id);
      })->with('Bills')->get();



      //get all_sum
      $all_sum=$Cases=Cases::whereHas('Doctors', function ($query) use($Doctor_id) {
        $query->where('doctors.id','=',$Doctor_id);
    })->with('Bills')->get()->sum('price');

      $Cases=Cases::whereHas('Doctors', function ($query) use($Doctor_id) {
        $query->where('doctors.id','=',$Doctor_id);
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




      $user=User::where('clinic_id','=',auth()->user()->clinic_id)->where('role_id','=',2)->orwhere('role_id','=',5)->get();
      $doctor=Doctors::where('user_id','=',$user[0]->id)->get();
      $Doctor_id=$doctor[0]->id;

      $from=$request->from_date;
      $to=$request->to_date;

      $patients = patients::whereHas('Cases', function ($query) use($to,$from) {
        $query->whereBetween('created_at', [$from,$to]);
       })->with(['Cases.Bills','Case.CaseCategories','Cases.CaseCategories'])
       ->whereHas('Cases.Doctors', function ($query) use($Doctor_id) {
        $query->where('doctors.id','=',$Doctor_id);
    })
      ->get();



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
