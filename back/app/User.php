<?php
namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{

    use Notifiable;

    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $fillable = [
        'full_name', 'role_id', 'status_id', 'mobile_token', 'user_email'];

    protected $hidden = [
        'password'
    ];
    public function image()
    {
        return $this->morphOne('App\Image', 'imageable');
    }
    public function address()
    {
        return $this->morphOne('App\Address', 'addressable');
    }


    public function scopeGetStatusCount($query, $status, $role_id)
    {
     
        return $query->WhereHas('status', function ($query) use ($status) {
            $query->where('status_name', $status)->where('status_type_id','=',1);
        })->where("role_id",$role_id)->count();
    }

    public function ownerStaff()
    {
        return $this->hasOne('App\OwnerStaff');
    }

    public function role()
    {
        return $this->belongsTo('App\Role');
    }

    public function reservation()
    {
        return $this->hasMany('App\Reservation');
    }



    public function DevicesTokens()
    {
        return $this->hasMany('App\DevicesTokens');
    }


    public function Notification()
    {
        return $this->morphOne('App\Notification', 'Notificationable');
    }

    public function comments()
    {
        return $this->hasMany('App\Comment');
    }

    public function notifications()
    {
        return $this->hasMany('App\Notification');
    }

    public function payment()
    {
        return $this->hasMany('App\Payment');
    }

    public function delivery()
    {
        return $this->hasMany('App\Delivery');
    }

    public function status()
    {
        return $this->belongsTo('App\Status');
    }

     public function scopeActive($query, $u)
    {
    return $u->role->role_name;
    }
    public function otp()
    {
        return $this->hasMany('App\Otp');
    }

    public function driver()
    {
        return $this->hasOne('App\Driver');
    }

    public function rates()
    {
        return $this->hasMany('App\Rate');
    }

     public function getJWTIdentifier()
     {
        return $this->getKey();
     }

     public function getJWTCustomClaims()
     {
       return [];
     }

    public function owner()
    {
        return $this->hasOne('App\Owner');
    }
}
