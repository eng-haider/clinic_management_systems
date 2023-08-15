<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DeliveryPrice extends Model
{
    protected $guarded = [];
    protected $hidden = [
        
    ];
    public function province()
    {
        return $this->belongsTo('App\Province');
    }

    public function owner()
    {
        return $this->belongsTo('App\Owner');
    }

    public function reservation()
    {
        return $this->hasOne('App\Reservation');
    }

}
