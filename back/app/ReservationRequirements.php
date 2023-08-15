<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ReservationRequirements extends Model
{

    protected $table = 'reservation_requirements';

    protected $primaryKey = 'id';


    protected $guarded = [];
    protected $hidden = [
        'updated_at', 'created_at',
    ];

    public function resrvation()
    {
        return $this->belongsTo('App\Reservation');
    }

    public function image()
    {
        return $this->morphOne('App\Image', 'imageable');
    }
    

    public function item()
    {
        return $this->belongsTo('App\Item');
    }

    

    public function ItemsReservationRequirements()
    {
        return $this->belongsTo('App\ItemsReservationRequirements');
    }

}
