<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DoctorPatient extends Model
{
    use HasFactory;

    protected $table = 'doctorpatient';



    public $timestamps = false;
    protected $fillable = [
        'patients_id','doctors_id'
    ];


}
