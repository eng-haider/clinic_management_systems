<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    protected $guarded = [];

    protected $hidden = [
        'updated_at', 'created_at',
    ];

    public function brand_agency_owner()
    {
        return $this->hasMany('App\BrandAgencyOwner');
    }

    public function image()
    {
        return $this->morphOne('App\Image', 'imageable');
    }
}
