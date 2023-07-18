<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Cases extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id','name','notes','price','upper_right','upper_left','lower_right','lower_left','patient_id','case_categores_id','status_id','tooth_num'
    ];

    public function Patient()
    {
        return $this->belongsTo(Patients::class);
    }

    public function images()
    {
        return $this->morphMany(images::class, 'imageable');
    }

    public function Cases()
    {
        return $this->hasMany(Cases::class, 'patient_id', 'id');
    }

    public function Sessions()
    {
        return $this->hasMany(Sessions::class, 'case_id', 'id');
    }



    public function Doctors()
    {
        return $this->belongsToMany(Doctors::class, 'CaseDoctor');
    }


    public function Bills()
    {
        return $this->morphMany(Bills::class, 'billable');
    }


    public function billsx()
    {
        return $this->morphMany(Bills::class, 'billable');
    }

    public function status()
    {
        return $this->belongsTo(Status::class);

    }


    public function CaseCategories()
    {
        return $this->belongsTo(CaseCategories::class, 'case_categores_id', 'id');
    }




}
