<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OwnerType extends Model
{
    protected $table = 'owner_type';

    protected $primaryKey = 'id';

    protected $fillable = [
        'owner_type_name'
    ];

    public function owner_type()
    {
        return $this->hasOne('App\Owner');
    }
}
