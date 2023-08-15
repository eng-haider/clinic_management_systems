<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SubCategory extends Model
{
    protected $table = 'sub_categories';

    protected $primaryKey = 'id';

    protected $fillable = ['sub_category_name','category_id'];

    public function category()
    {
        return $this->belongsTo('App\Category','category_id');
    }

    public function item()
    {
        return $this->hasOne('App\Item');
    }

    public function image()
    {
        return $this->morphOne('App\Image', 'imageable');
    }

    public function LastCategory()
    {
        return $this->hasMany('App\LastCategory');
    }
}
