<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Province extends Model
{
    protected $table = 'provinces';

    protected $primaryKey = 'id';

    protected $fillable = ['province_name','country_id'];

    public function country()
    {
        return $this->belongsTo('App\Country');
    }

    public function Address()
    {

        return $this->hasOne('App\Address');

    }

    public function delivery_prices()
    {
        return $this->hasOne('App\DeliveryPrices');
    }
}
