<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ItemsAddresses extends Model
{
    protected $table = 'address_item';

    public function address()
    {
        return $this->belongsTo(Address::class);
     
    }

}
