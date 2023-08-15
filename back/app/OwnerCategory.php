<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OwnerCategory extends Model
{
    protected $table = 'owner_categories';

    protected $primaryKey = 'id';

    protected $fillable = [
        'owner_id',
        'category_id'
        ,'status_id'
    ];

    public function category()
    {
        return $this->belongsTo('App\Category');
    }

    public function status()
    {
        return $this->belongsTo('App\Status');
    }

    public function owner()
    {
        return $this->belongsTo('App\Owner');
    }


}
