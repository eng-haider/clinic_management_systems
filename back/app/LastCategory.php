<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LastCategory extends Model
{
    
    protected $guarded = [];
    
    public function sub_category()
    {
        return $this->belongsTo('App\SubCategory');
    }

    public function item()
    {
        return $this->hasOne('App\Item');
    }

    public function image()
    {
        return $this->morphOne('App\Image', 'imageable');
    }
}
