<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Items extends Model
{
    use HasFactory;

    protected $table = 'items';
    protected $fillable = [
        'name','quantity','required_quantity','clinics_id'
    ];

    public function Clinics()
    {
        return $this->belongsTo(Clinics::class);
    }




}
