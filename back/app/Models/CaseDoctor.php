<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CaseDoctor extends Model
{

protected $table = 'casedoctor';
    use HasFactory;


    public $timestamps = false;
    protected $fillable = [
        'cases_id','doctors_id'
    ];
}
