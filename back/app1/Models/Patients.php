<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class patients extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name','age','phone','email','sex','note','systemic_conditions','user_id','doctor_id'
    ];



    public function Case()
    {
        return $this->hasOne(Cases::class, 'patient_id', 'id')->latest();
    }
    public function Cases()
    {
        return $this->hasMany(Cases::class, 'patient_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }


    public function Doctors()
    {
        return $this->belongsToMany(Doctors::class, 'DoctorPatient');
    }


    public function getUserIdsAttribute()
    {
        return $this->Doctors;
    }



}
