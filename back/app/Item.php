<?php

namespace App;
use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\AllowedFilter;
use Illuminate\Support\Facades\Http;
use Request;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    protected $table = 'items';

    protected $primaryKey = 'id';

    protected $fillable = ['item_name', 'item_description', 'extra_details'
        , 'sub_category_id', 'owner_id', 'deliverable'];
    protected $hidden = [
        'updated_at', 'created_at', 'status_id'
    ];

    //protected $with = ['price'];

    public function bill()
    {
        return $this->hasMany('App\Bills');
    }


    public function addresses()
    {
        return $this->belongsToMany(addresses::class);
    }


    

    public function LastCategory()
    {
        return $this->belongsTo('App\LastCategory');
    }
    public function SubCategory()
    {
        return $this->belongsTo('App\SubCategory');
    }

    public function owner()
    {
        return $this->belongsTo('App\Owner');
    }

    public function deliverable()
    {
        return $this->belongsTo('App\deliverable');
    }


    

    public function status()
    {
        return $this->belongsTo('App\Status');
    }

    // public function address()
    // {
    //     return $this->morphOne('App\Address', 'addressable');
    // }

    public function address()
    {
        return $this->belongsToMany(Address::class);

    }


    public function price()
    {
        return $this->hasOne('App\Price');
    }
    public function rate()
    {
        return $this->morphMany('App\Rate', 'rateable');
    }

    public function image()
    {
        return $this->morphMany('App\Image', 'imageable');
    }
    
        public function advertise()
    {
        return $this->morphMany('App\Advertising', 'advertisingable');
    }


    public function reservation()
    {
        return $this->hasMany('App\Reservation');
    }

   /* public function day_to_open()
    {
        return $this->hasMany('App\DayToOpen');
    }*/

    public function offer()
    {
        return $this->hasMany('App\Offer');
    }

    public function item_features()
    {
        return $this->hasMany('App\ItemFeatures');
    }



    public function ItemsReservationRequirements()
    {
        return $this->hasMany('App\ItemsReservationRequirements');
    }

    public function comments()
    {
        return $this->hasMany('App\Comment');
    }

    public function delivery()
    {
        return $this->hasMany('App\Delivery');
    }

    public function day_to_open()
    {
        return $this->morphMany('App\DayToOpen','day_to_open_able');
    }

    public function  scopeGetoffers(Builder $query,$date): Builder
    
    {

       return $query->WhereHas('offer', function ($query)  {
              $query->where('status_id', '=',19)
              ->where('is_valid', '=',1);
          });


    }

    public function favorite()
    {
        return $this->morphMany('App\Favorite','favoriteable');
        
    }
   


    public function scopeGetByCityZipCode(Builder $query,$data): Builder
    

    {

       Request::ip();
       $addressInfo_api = Http::get('http://api.ipinfodb.com/v3/ip-city/?key=882470ff133da824f8ae5a824b3eeb580eda9e657936e80f98d33e20d51cfd72&ip=37.238.160.45&format=json');
       $addressInfo = json_decode($addressInfo_api, true);
       $zipcode= $addressInfo['zipCode'];
      
        return $query->WhereHas('address.province', function ($query)use($zipcode)  {
            $query->where('zipCode', '=',$zipcode)
           ;
        });

    }


  

}
