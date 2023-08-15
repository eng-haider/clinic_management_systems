<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OwnerSubscriptions extends Model
{

    protected $table = 'owner_subscriptions';

    protected $primaryKey = 'id';


    public function owner()
    {
        return $this->belongsTo('App\Owner');
    }

    public function status()
    {
        return $this->belongsTo('App\Status');
    }

    public function bill()
    {
        return $this->belongsTo('App\Bills');
    }


    public function subscription_package()
    {
        return $this->belongsTo('App\SubscriptionPackages');
    }
}
