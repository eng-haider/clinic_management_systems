<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SubTime extends Model
{
    
    protected $guarded = [];

    public function time_to_open()
    {
        return $this->belongsTo('App\TimeToOpen');
    }

    public function reservation()
    {
        return $this->hasMany('App\Reservation');
    }

}
