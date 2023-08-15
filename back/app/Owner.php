<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Owner extends Model
{
    protected $guarded = [];

    public function status()
    {
        return $this->belongsTo('App\Status');
    }

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function advertise()
    {
        return $this->morphMany('App\Advertising', 'advertisingable');
    }
    
    public function item()
    {
        return $this->hasMany('App\Item');
    }

    public function ownerStaff()
    {
        return $this->hasMany('App\OwnerStaff');
    }

    public function ownerCategory()
    {
        return $this->hasOne('App\OwnerCategory');
    }


    public function delivery()
    {
        return $this->hasMany('App\Delivery');
    }

   public function delivery_prices()
    {
        return $this->hasMany('App\DeliveryPrice');
    }

    public function image()
    {
        return $this->morphMany('App\Image', 'imageable');
    }
    public function address()
    {
        return $this->morphMany('App\Address','addressable');
    }
    public function rate()
    {
        return $this->morphMany('App\Rate','rateable');
    }

    public function favorite_delivery()
    {
        return $this->hasMany('App\Favorites', 'owner_delivery_id');
    }

    public function favorite_item()
    {
        return $this->hasMany('App\Favorites', 'owner_item_id');
    }

    public function driver()
    {
        return $this->hasMany('App\Driver');
    }

    public function owner_category()
    {
        return $this->belongsTo('App\OwnerCategory');
    }

    public function owner_type()
    {
        return $this->belongsTo('App\OwnerType');
    }

    public function CurrencyTransfers()
    {
        return $this->hasOne('App\CurrencyTransfers')->latest();
    }


    
    public function brand_agency_owner()
    {
        return $this->hasMany('App\BrandAgencyOwner');
    }

    public function OwnerSubscriptions()
    {
        return $this->hasMany('App\OwnerSubscriptions');
    }

    public function currentOwnerSubscription()
    {

        return $this->hasMany('App\OwnerSubscriptions')->latest();

    }



    

    public function day_to_open()
    {
        return $this->morphMany('App\DayToOpen','day_to_open_able');
    }

    public function partnership()
    {
        return $this->hasOne('App\Partnership','owner_items_id');
        
    }




    public function scopeDelveries($query,$status)
    {
        return $query->where('status',function($query) use($status){
            $query->where('status_name',$status);
         })->where('owner_type_id',2);
    }

    public function scopeItems($query,$status)
    {
        return $query->where('status',function($query) use($status){
            $query->where('status_name',$status);
         })->where('owner_type_id',2);
    }

}
