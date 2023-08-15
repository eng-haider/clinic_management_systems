<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
    protected $table = 'offers';
    protected $primaryKey = 'id';
    protected $fillable = [
        'offer_name','item_id',
        'discount_percentage','offer_price',
        'offer_description','offer_from_date',
        'offer_to_date','status_id'
    ];

    public function item()
    {
        return $this->belongsTo('App\Item');
    }

    public function Notification()
    {
        return $this->morphOne('App\Notification', 'Notificationable');
    }
    

   

    public function status()
    {
        return $this->belongsTo('App\Status');
    }
}
