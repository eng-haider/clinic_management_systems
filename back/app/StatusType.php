<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StatusType extends Model
{
    protected $table = 'status_type';

    protected $primaryKey = 'id';

    protected $fillable = ['status_type_name'];


    public function status()
    {
        return $this->hasMany('App\Status');
    }
}
