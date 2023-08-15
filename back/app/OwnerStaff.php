<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OwnerStaff extends Model
{
    protected $table = 'owner_staff';

    protected $primaryKey = 'id';

    protected $fillable = ['user_id','owner_id'];

    public function owner()
    {
        return $this->belongsTo('App\Owner');
    }

    public function user()
    {
        return $this->belongsTo('App\User');
    }
}
