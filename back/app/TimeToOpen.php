<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TimeToOpen extends Model
{
    protected $table = 'time_to_open';

    protected $primaryKey = 'id';

    protected $fillable = ['day_to_open_id','from_time','to_time','reservations_number','every_time','max_request','status_id','reservation_type_id'];

    public function day_to_open()
    {
        return $this->belongsTo('App\DayToOpen');
    }

    public function status()
    {
        return $this->belongsTo('App\Status');
    }

    public function reservation_type()
    {
        return $this->belongsTo('App\reservation_types');
    }

    public function SubTime()
    {
        return $this->hasMany('App\SubTime');
    }
}
