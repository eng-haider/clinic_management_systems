<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    protected $table = 'statuses';

    protected $primaryKey = 'id';

    protected $fillable = ['status_name','status_name_ar','status_type_id'];
    protected $hidden = [
        'updated_at', 'created_at',
    ];

    public function bill()
    {
        return $this->hasMany('App\Bills');
    }
    public function user()
    {
        return $this->hasOne('App\User');
    }

    public function owner()
    {
        return $this->hasOne('App\Owner');
    }

    public function item()
    {
        return $this->hasOne('App\Item');
    }

    public function reservation()
    {
        return $this->hasOne('App\Reservation');
    }

    public function offer()
    {
        return $this->hasOne('App\Offer');
    }

    public function delivery()
    {
        return $this->hasOne('App\Delivery');
    }

    public function status_type()
    {
        return $this->belongsTo('App\StatusType');
    }

    public function driver()
    {
        return $this->hasMany('App\Driver');
    }
}
