<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ReservationFeature extends Model
{
    protected $guarded = [];
    protected $hidden = [
        'updated_at', 'created_at',
    ];

    public function resrvation()
    {
        return $this->belongsTo('App\Reservation');
    }

    public function item()
    {
        return $this->belongsTo('App\Item');
    }

    public function ItemFeature()
    {
        return $this->belongsTo('App\ItemFeatures');
    }


}
