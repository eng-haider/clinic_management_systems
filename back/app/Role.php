<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = 'roles';

    protected $primaryKey = 'id';

    protected $fillable = ['role_name'];

    public function user()
    {
        return $this->hasOne('App\User');
    }
}
