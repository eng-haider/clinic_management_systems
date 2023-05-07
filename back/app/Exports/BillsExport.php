<?php

namespace App\Exports;

use App\Models\Bills;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use App\Models\Patients;
use App\Models\Cases;
use App\Models\Doctors;



use App\Models\User;


class BillsExport implements FromQuery, WithHeadings, WithMapping

{
   
    use Exportable;
    private $Doctor_id;
    /**
    * @return \Illuminate\Support\Collection
    */
   
    public function __construct(int $Doctor_id)
    {
        $this->Doctor_id = $Doctor_id;
    }


    public function headings(): array
    {
        return [
            'patient_name',
        ];
    }


  

    public function map($patients): array
    {
       

       
  
        
        return [
            'Patient_name' => 'xx',
            

        ];
    }


    public function query()
    {
       
        
        return  Patients::query();


    }

}
