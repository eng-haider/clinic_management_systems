<?php

namespace App;
use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\AllowedFilter;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    protected $guarded = [];
    protected $hidden = [
        'updated_at', 'created_at',
    ];
    public function address()
    {
        return $this->morphOne('App\Address', 'addressable');
    }
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

    public function delivery()
    {
        return $this->hasMany('App\Delivery');
    }

    public function bill()
    {
        return $this->hasOne('App\Bills');
    }

    public function reservationFeatures()
    {
        return $this->hasMany('App\ReservationFeature');  
    }

    public function ReservationRequirements()
    {
        return $this->hasMany('App\ReservationRequirements');  
    }

    

    public function delivery_price()
    {
        return $this->belongsTo('App\DeliveryPrice');
    }

    public function scopeOwnerItem($query, $owner_id)
    {
        return $query->WhereHas('item', function ($query) use ($owner_id) {
            $query->where('owner_id', '=', $owner_id);
        });
    }

    

    public function scopeGetBetweenDates($query, $first, $second)
    {
        return $query->whereDateBetween('reservation_start_date', [$first, $second]);
    }

    public function Notification()
    {
        return $this->morphOne('App\Notification', 'Notificationable');
    }


    public function image()
    {
        return $this->morphMany('App\Image', 'imageable');
    }
    public function scopeWithRelated($query)
    {
        return $query->with([
            'user:id,full_name','item.owner:id,owner_barnd_name,owner_email,owner_phone',
            'item.address.province:id,province_name', 'status:id,status_name,status_name_ar,status_color,status_icon',
            'delivery_price:id,delivery_price_value', 'address','item.image:id,image_url']);   
    }

    public function scopeGetStatusCount($query, $status, $arr)
    {
     
        return $query->WhereHas('status', function ($query) use ($status) {
            $query->where('status_name', $status);
        })->whereIn("item_id", $arr)->count();
    }
    public function scopeGetStatusCountDelivery($query, $status, $owner_id)
    {
        return $query->whereHas('reservation.status',function($query) use($status,$owner_id){
            $query->where('status_name',$status);
         })->where('owner_delivery_id',$owner_id)
         ->count();
    }

    public function scopeBetweenDate(Builder $query,$date): Builder
    {

        $date=explode("_",$date);

        return $query ->whereBetween('reservation_start_date', [$date[0],$date[1]]);
    }

    public function scopeBetweenTime(Builder $query,$time): Builder
    {


        $timestamp = strtotime($time) + 60*60;
        $time2 = date('H:i', $timestamp);
        return $query ->whereTime('reservation_from_time','<=',$time)
                      ->whereTime('reservation_to_time','>',$time);
    }
}
