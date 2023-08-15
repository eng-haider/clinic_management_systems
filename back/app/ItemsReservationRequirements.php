<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ItemsReservationRequirements extends Model
{
    protected $table = 'item_reservation_requirements';

    protected $primaryKey = 'id';

    protected $fillable = ['item_id','requirement_name'];

    public function item()
    {
        return $this->belongsTo('App\Item');
    }

    public function ReservationRequirements()
    {
        return $this->hasOne('App\ReservationRequirements');
    }


   
}
