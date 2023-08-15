<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Delivery extends Model
{
    protected $guarded = [];


    public function owner_delivery()
    {
        return $this->belongsTo('App\Owner');
    }

    public function reservation()
    {
        return $this->belongsTo('App\Reservation');
    }

    public function status()
    {
        return $this->belongsTo('App\Status');
    }

    public function deliveries_driver()
    {
        return $this->hasMany('App\DeliveryDriver');
    }

    public function scopeGetStatusCountDelivery($query, $status, $owner_id)
    {
        return $query->whereHas('reservation.status',function($query) use($status,$owner_id){
            $query->where('status_name',$status);
         })->where('owner_delivery_id',$owner_id)
         ->count();
    }

}
