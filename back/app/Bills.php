<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Bills extends Model
{
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function item()
    {
        return $this->belongsTo('App\Item');
    }

    public function status()
    {
        return $this->belongsTo('App\Status');
    }

    public function tasdid_bills()
    {
        return $this->belongsTo('App\TasdidBills','payId','PayId');
    }

    public function PaymentMethods()
    {
        return $this->belongsTo('App\PaymentMethods','payment_method_id');
    }

    

    
}
