<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class reservation_types extends Model
{
 
    public function DayToOpen()
    {
        return $this->hasOne('App\DayToOpen');
    }
}
