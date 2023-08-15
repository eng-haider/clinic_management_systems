<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SubscriptionPackages extends Model
{
    public function OwnerSubscriptions()
    {
        return $this->hasOny('App\OwnerSubscriptions');
    }

}
