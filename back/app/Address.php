<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $guarded = [];

    protected $hidden = [
        'updated_at', 'created_at'
    ];

    public function addressable(){

        return $this->morphTo('addressable');
    }

    
    

    public function ItemsAddresses()
    {
        return $this->HasMany(ItemsAddresses::class);

    }

    public function province()
    {
        return $this->belongsTo('App\Province');
    }


}
