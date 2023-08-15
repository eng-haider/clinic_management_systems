<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DeliveryDriver extends Model
{
  protected $table = 'deliveries_driver';
    protected $guarded = [];

    public function delivery()
    {
        return $this->belongsTo('App\Delivery');
    }

    public function driver()
    {
        return $this->belongsTo('App\Driver');
    }

    public function status()
    {
        return $this->belongsTo('App\Status');
    }
}
