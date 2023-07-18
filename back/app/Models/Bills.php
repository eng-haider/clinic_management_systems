<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class bills extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'price','PaymentDate'
    ];

    public function Billsble(){

        return $this->morphTo();
    }


    public function case()
    {
        return $this->belongsTo(Cases::class, 'billable_id', 'id');
    }



}
