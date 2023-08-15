<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Advertising extends Model
{
    protected $guarded = [];

    protected $hidden = [
        'updated_at', 'created_at','advertisingable_id','status_id'
    ];

    public function advertisingable(){

        return $this->morphTo();
    }
    public function status()
    {
        return $this->belongsTo('App\Status');
    }

    public function Notification()
    {
        return $this->morphOne('App\Notification', 'Notificationable');
    }

    public function owner()
    {
        return $this->belongsTo('App\Owner');
    }
    public function advertisingType()
    {
        return $this->belongsTo('App\AdvertisingType');
    }
    public function image()
    {
        return $this->morphMany('App\Image', 'imageable');
    }

}
