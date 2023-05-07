<?php

namespace App\Exports;

use App\Models\Bills;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class UsersExport implements FromCollection, WithHeadings, WithMapping

{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Bills::all();
    }

    public function headings(): array
    {
        return [
            'Patient name',
            'CaseNumber',
            'Totel',
            'Paid',
            'Remaining amount',
        ];
    }

    public function map($logs): array
    {
       

        $user=User::where('clinic_id','=',auth()->user()->clinic_id)->where('role_id','=',2)->get();
        $doctor=Doctors::where('user_id','=',$user[0]->id)->get();
        $Doctor_id=$doctor[0]->id;
  
        
        $patients = patients::with(['Cases.Bills','Case.CaseCategories','Cases.CaseCategories'  ])   
        ->where('doctor_id','=',$doctor[0]->id)->orderBy('id', 'desc')->get();
  
        
        return [
            'Patient name' => $user_id,
            'CaseNumber' => $tickets_id,
            'Totel' => $status,
            'Paid'=> $logs->subject,
            'Remaining amount' => $logs->created_at

        ];
    }



}
