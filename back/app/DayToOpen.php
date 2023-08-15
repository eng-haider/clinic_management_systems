<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DayToOpen extends Model
{
    protected $table = 'day_to_open';

    protected $primaryKey = 'id';

    protected $fillable = ['item_id','work_day','every_day','status_id','reservations_number','reservation_type_id'];
    public function item()
    {
        return $this->belongsTo('App\Item');
    }

    public function time_to_open()
    {
        return $this->hasMany('App\TimeToOpen');
    }

    public function day_to_open_able(){

        return $this->morphTo();
    }

    public function status()
    {
        return $this->belongsTo('App\Status');
    }

    public function reservation_type()
    {
        return $this->belongsTo('App\reservation_types');
    }

}
