<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    protected $guarded = [];

    public function province()
    {
        return $this->belongsTo('App\Province');
    }

    public function Address()
    {

        return $this->hasOne('App\Address');

    }

}
