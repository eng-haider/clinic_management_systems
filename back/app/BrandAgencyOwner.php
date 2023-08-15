<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BrandAgencyOwner extends Model
{
    protected $guarded = [];

    protected $hidden = [
        'updated_at', 'created_at',
    ];

    public function owner()
    {
        return $this->belongsTo('App\Owner');
    }

    public function brand()
    {
        return $this->belongsTo('App\Brand');
    }
}
