<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AdvertisingType extends Model
{
    protected $guarded = [];

    protected $hidden = [
        'updated_at', 'created_at'
    ];

     public function advertising()
     {
         return $this->hasMany('App\Advertising');
     }
}
