<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Partnership extends Model
{
  protected $guarded=[];




    public function owner_delivery()
    {
      return $this->belongsTo('App\Owner');
    }





    public function owner_items()
    {
          return $this->belongsTo('App\Owner');
    }
    public function status()
    {
        return $this->belongsTo('App\Status');
    }
}
