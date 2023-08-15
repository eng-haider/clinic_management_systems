<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table = 'categories';

    //protected $primaryKey = 'id';

  // protected $fillable = ['id,category_name'];

    public function sub_category()
    {
        return $this->hasMany('App\SubCategory');
    }

    public function owner_catgory()
    {
        return $this->hasMany('App\OwnerCategory');
    }

    public function image()
    {
        return $this->morphOne('App\Image', 'imageable');
    }
}
