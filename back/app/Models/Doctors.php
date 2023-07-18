<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class doctors extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name','clinics_id','user_id'
    ];

    public function Clinics()
    {
        return $this->belongsTo(Clinics::class);
    }

    public function Cases()
    {
        return $this->belongsToMany(Cases::class, 'CaseDoctor');
    }


    public function casesx()
    {
        return $this->belongsToMany(Cases::class, 'CaseDoctor');
    }

    public function Patients()
    {
        return $this->belongsToMany(Patients::class, 'DoctorPatient');
    }



}
