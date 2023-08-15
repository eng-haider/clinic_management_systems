<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ItemFeatures extends Model
{
    protected $table = 'item_features';

    protected $primaryKey = 'id';

    protected $fillable = ['item_id','feature_name','feature_price'];

    public function item()
    {
        return $this->belongsTo('App\Item');
    }

    public function reservation()
    {
        return $this->hasMany('App\Reservation');
    }
}
